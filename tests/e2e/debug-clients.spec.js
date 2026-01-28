import { test } from '@playwright/test';

test('Debug clients page', async ({ page }) => {
  // Login first
  await page.goto('http://localhost:8080/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');
  await page.waitForLoadState('networkidle');
  
  console.log('After login URL:', page.url());
  
  // Navigate to clients page
  const clientsUrl = 'http://localhost:8080/wp-admin/admin.php?page=wappointment-clients';
  console.log('Navigating to:', clientsUrl);
  await page.goto(clientsUrl);
  await page.waitForLoadState('networkidle');
  
  console.log('Clients page URL:', page.url());
  console.log('Clients page Title:', await page.title());
  
  // Check if React root exists
  const reactRoot = await page.locator('#wappointment-react-root').count();
  console.log('React root found:', reactRoot > 0);
  
  // Check if React root has content
  if (reactRoot > 0) {
    const rootContent = await page.locator('#wappointment-react-root').innerHTML();
    console.log('React root HTML length:', rootContent.length);
    console.log('React root HTML:', rootContent);
  }
  
  // Listen for console messages
  page.on('console', msg => {
    console.log(`[Browser ${msg.type()}]:`, msg.text());
  });
  
  // Listen for errors
  page.on('pageerror', error => {
    console.log('[Browser error]:', error.message);
  });
  
  await page.screenshot({ path: 'debug-clients-page.png', fullPage: true });
  console.log('Screenshot saved: debug-clients-page.png');
  
  // Wait for React to potentially load
  console.log('Waiting 3 seconds for React to load...');
  await page.waitForTimeout(3000);
  
  const rootContentAfterWait = await page.locator('#wappointment-react-root').innerHTML();
  console.log('React root HTML after wait length:', rootContentAfterWait.length);
  console.log('React root HTML after wait:', rootContentAfterWait);
  
  await page.screenshot({ path: 'debug-clients-page-after-wait.png', fullPage: true });
  console.log('Screenshot saved: debug-clients-page-after-wait.png');
});
