import { test } from '@playwright/test';

test('Debug create client flow', async ({ page }) => {
  // Login
  await page.goto('http://localhost:8080/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');
  await page.waitForLoadState('networkidle');
  
  // Go to clients page
  await page.goto('http://localhost:8080/wp-admin/admin.php?page=wappointment-clients');
  await page.waitForSelector('.wappointment-app');
  
  console.log('Before clicking Create Client');
  await page.screenshot({ path: 'before-create-click.png', fullPage: true });
  
  // Click Create Client
  await page.click('button:has-text("Create Client")');
  
  // Wait a bit
  await page.waitForTimeout(1000);
  
  console.log('After clicking Create Client');
  await page.screenshot({ path: 'after-create-click.png', fullPage: true });
  
  // Check what's on the page now
  const html = await page.content();
  console.log('Page HTML length:', html.length);
  
  // Look for modal, dialog, or form
  const modalVisible = await page.locator('[role="dialog"], .modal, .form-modal').count();
  console.log('Modals found:', modalVisible);
  
  // Look for any form inputs
  const nameInputs = await page.locator('input[name="name"], input[placeholder*="Name"]').count();
  console.log('Name inputs found:', nameInputs);
  
  if (nameInputs > 0) {
    const nameInput = page.locator('input[name="name"], input[placeholder*="Name"]').first();
    const isVisible = await nameInput.isVisible();
    console.log('First name input visible:', isVisible);
    
    if (isVisible) {
      console.log('Name input selector:', await nameInput.evaluate(el => el.outerHTML));
    }
  }
  
  // Look for ALL buttons
  const allButtons = await page.locator('button').all();
  console.log('Total buttons found:', allButtons.length);
  for (let i = 0; i < allButtons.length; i++) {
    const text = await allButtons[i].textContent();
    const visible = await allButtons[i].isVisible();
    const html = await allButtons[i].evaluate(el => el.outerHTML.substring(0, 200));
    console.log(`Button ${i}: visible=${visible}, text="${text}", html=${html}`);
  }
  
  // Check console for any errors
  page.on('console', msg => console.log('[Browser]:', msg.text()));
  page.on('pageerror', err => console.log('[Error]:', err.message));
  
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'after-wait.png', fullPage: true });
});
