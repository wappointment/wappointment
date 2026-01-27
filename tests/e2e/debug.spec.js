import { test, expect } from '@playwright/test';

test('debug WordPress setup', async ({ page }) => {
  // Check if WordPress is accessible
  await page.goto('http://localhost:8080');
  console.log('Homepage URL:', page.url());
  console.log('Homepage Title:', await page.title());
  
  // Try login page
  await page.goto('http://localhost:8080/wp-login.php');
  await page.waitForLoadState('networkidle');
  console.log('Login page URL:', page.url());
  console.log('Login page Title:', await page.title());
  
  // Take screenshot
  await page.screenshot({ path: 'test-results/debug-login-page.png', fullPage: true });
  
  // Check if login form exists
  const loginFormExists = await page.locator('#user_login').isVisible();
  console.log('Login form visible:', loginFormExists);
  
  if (loginFormExists) {
    // Try to login
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('password');
    await page.screenshot({ path: 'test-results/debug-before-submit.png', fullPage: true });
    
    await page.locator('#wp-submit').click();
    
    // Wait a bit
    await page.waitForTimeout(2000);
    await page.screenshot({ path: 'test-results/debug-after-submit.png', fullPage: true });
    
    console.log('After login URL:', page.url());
    console.log('After login Title:', await page.title());
    
    // Check for errors
    const errorMessage = await page.locator('#login_error').textContent().catch(() => null);
    if (errorMessage) {
      console.log('Login error:', errorMessage);
    }
  }
});
