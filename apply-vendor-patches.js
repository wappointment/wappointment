#!/usr/bin/env node

/**
 * Script to apply patches to vendor files
 * Reads configuration from vendor-patches.json and applies prepend/append operations
 */

const fs = require('fs');
const path = require('path');

// Configuration file path (can be overridden with CONFIG_FILE env variable)
const CONFIG_FILE = process.env.CONFIG_FILE 
  ? path.join(__dirname, process.env.CONFIG_FILE)
  : path.join(__dirname, 'vendor-patches.json');

// Colors for console output
const colors = {
  reset: '\x1b[0m',
  bright: '\x1b[1m',
  green: '\x1b[32m',
  yellow: '\x1b[33m',
  red: '\x1b[31m',
  cyan: '\x1b[36m'
};

/**
 * Log messages with colors
 */
function log(message, color = 'reset') {
  console.log(`${colors[color]}${message}${colors.reset}`);
}

/**
 * Read and parse the configuration file
 */
function readConfig() {
  try {
    if (!fs.existsSync(CONFIG_FILE)) {
      log(`Configuration file not found: ${CONFIG_FILE}`, 'red');
      process.exit(1);
    }

    const configContent = fs.readFileSync(CONFIG_FILE, 'utf8');
    return JSON.parse(configContent);
  } catch (error) {
    log(`Error reading configuration file: ${error.message}`, 'red');
    process.exit(1);
  }
}

/**
 * Apply a single pattern/match to content
 */
function applyPattern(content, patternStr, prepend, append, regex, flags) {
  // Create search pattern (regex or literal string)
  let searchPattern;
  if (regex) {
    try {
      searchPattern = new RegExp(patternStr, flags || 'g');
    } catch (error) {
      log(`  ⚠ Invalid regex pattern: ${patternStr}`, 'yellow');
      return { content, applied: false };
    }
  } else {
    // Escape special regex characters for literal string matching
    const escapedPattern = patternStr.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    searchPattern = new RegExp(escapedPattern, 'g');
  }

  // Count matches
  const matchCount = (content.match(searchPattern) || []).length;
  
  if (matchCount === 0) {
    log(`  ⚠ Pattern not found: ${patternStr}`, 'yellow');
    return { content, applied: false };
  }

  // Build replacement string
  let replacement = '';
  if (prepend) {
    replacement += prepend;
  }
  replacement += patternStr;
  if (append) {
    replacement += append;
  }

  // Apply the patch
  const newContent = content.replace(searchPattern, replacement);
  
  if (newContent !== content) {
    log(`  ✓ Applied patch to: ${patternStr} (${matchCount} occurrence${matchCount > 1 ? 's' : ''})`, 'green');
    return { content: newContent, applied: true };
  }

  return { content, applied: false };
}

/**
 * Apply patches to a single file
 */
function patchFile(filePath, patches) {
  const fullPath = path.join(__dirname, filePath);

  // Check if file exists
  if (!fs.existsSync(fullPath)) {
    log(`  ⚠ File not found: ${filePath}`, 'yellow');
    return { success: false, error: 'File not found' };
  }

  try {
    // Read file content
    let content = fs.readFileSync(fullPath, 'utf8');
    let modified = false;
    let appliedPatches = 0;

    // Apply each patch configuration
    for (const patch of patches) {
      const { matches, prepend, append, regex, flags } = patch;

      // Handle backward compatibility: if 'pattern' exists (old format), use it
      if (patch.pattern) {
        const result = applyPattern(content, patch.pattern, prepend, append, regex, flags);
        content = result.content;
        if (result.applied) {
          modified = true;
          appliedPatches++;
        }
        continue;
      }

      // New format: 'matches' is an array of patterns
      if (!matches || !Array.isArray(matches)) {
        log(`  ⚠ Skipping patch: no matches specified`, 'yellow');
        continue;
      }

      if (!prepend && !append) {
        log(`  ⚠ Skipping patch: no prepend or append specified`, 'yellow');
        continue;
      }

      // Apply the same prepend/append to all matches
      for (const match of matches) {
        const result = applyPattern(content, match, prepend, append, regex, flags);
        content = result.content;
        if (result.applied) {
          modified = true;
          appliedPatches++;
        }
      }
    }

    // Write back to file if modified
    if (modified) {
      fs.writeFileSync(fullPath, content, 'utf8');
      return { success: true, appliedPatches };
    } else {
      return { success: false, error: 'No patches applied' };
    }

  } catch (error) {
    log(`  ✗ Error patching file: ${error.message}`, 'red');
    return { success: false, error: error.message };
  }
}

/**
 * Main function
 */
function main() {
  log('\n=== Vendor File Patcher ===\n', 'bright');

  // Read configuration
  const config = readConfig();

  if (!config.patches || !Array.isArray(config.patches)) {
    log('No patches found in configuration file', 'red');
    process.exit(1);
  }

  log(`Found ${config.patches.length} file(s) to patch\n`, 'cyan');

  let totalPatched = 0;
  let totalFailed = 0;

  // Process each file
  for (const fileConfig of config.patches) {
    const { file, patches } = fileConfig;

    if (!file || !patches || !Array.isArray(patches)) {
      log(`⚠ Skipping invalid patch configuration`, 'yellow');
      continue;
    }

    log(`Processing: ${file}`, 'bright');

    const result = patchFile(file, patches);
    
    if (result.success) {
      totalPatched++;
      log(`✓ Successfully patched ${file} (${result.appliedPatches} patch${result.appliedPatches > 1 ? 'es' : ''} applied)\n`, 'green');
    } else {
      totalFailed++;
      log(`✗ Failed to patch ${file}: ${result.error}\n`, 'red');
    }
  }

  // Summary
  log('\n=== Summary ===', 'bright');
  log(`Total files processed: ${config.patches.length}`, 'cyan');
  log(`Successfully patched: ${totalPatched}`, 'green');
  log(`Failed: ${totalFailed}`, totalFailed > 0 ? 'red' : 'cyan');
  log('');

  process.exit(totalFailed > 0 ? 1 : 0);
}

// Run the script
main();
