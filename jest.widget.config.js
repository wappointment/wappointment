const base = require('./jest.config');

module.exports = {
    ...base,
    roots: ['<rootDir>/resources/js-react-widget'],
    setupFilesAfterEnv: ['@testing-library/jest-dom'],
    collectCoverageFrom: ['resources/js-react-widget/**/*.{js,jsx}', '!**/__tests__/**'],
};
