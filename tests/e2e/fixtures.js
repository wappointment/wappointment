import { test as base } from '@playwright/test';

export const test = base.extend({
  // Authenticated context for admin user
  adminPage: async ({ browser }, use) => {
    const context = await browser.newContext();
    const page = await context.newPage();
    
    // Login to WordPress
    await page.goto('http://localhost:8080/wp-login.php');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Fill login form
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('password');
    
    // Click login button
    await page.locator('#wp-submit').click();
    
    // Wait for redirect to admin dashboard (more flexible)
    await page.waitForURL('**/wp-admin/**', { timeout: 10000 }).catch(async () => {
      // If redirect fails, check if we're already logged in
      const currentUrl = page.url();
      if (!currentUrl.includes('wp-admin')) {
        throw new Error(`Login failed. Current URL: ${currentUrl}`);
      }
    });
    
    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');
    
    await use(page);
    
    await context.close();
  },
});

export { expect } from '@playwright/test';
