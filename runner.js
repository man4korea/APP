require('dotenv').config();
const { spawn } = require('child_process');
const path = require('path');

// On Windows, using shell: true is convenient for finding `npx`,
// but it requires careful quoting of arguments containing special characters.
const command = 'npx';

// 1. Create the configuration object.
const configObject = {
    dataDir: path.join(__dirname, 'data')
};

// 2. Stringify it to a valid JSON string.
// e.g., {"dataDir":"C:\Users\...\data"}
const configJsonString = JSON.stringify(configObject);

// 3. CRITICAL STEP: Stringify the JSON string *again*.
// This wraps the whole thing in an outer layer of quotes and escapes the inner quotes,
// protecting it from being mangled by the Windows shell.
// e.g., "{\"dataDir\":\"C:\\\\Users\\\\...\\\\data\"}"
const shellProofConfig = JSON.stringify(configJsonString);

const args = [
    '-y',
    '@smithery/cli@latest',
    'run',
    // --- Options First ---
    '--key',
    '89871a9a-cf95-4de7-ae49-1d380312c282',
    '--profile',
    'evolutionary-termite-Omv5KV',
    '--config',
    shellProofConfig, // Pass the double-quoted, shell-proof string
    // --- Server Name Last ---
    '@cjo4m06/mcp-shrimp-task-manager'
];

const child = spawn(command, args, {
    stdio: 'inherit',
    shell: true // Necessary for `npx` resolution
});

child.on('close', (code) => {
    console.log(`Child process exited with code ${code}`);
});

child.on('error', (err) => {
    console.error('Failed to start child process.', err);
});