module.exports = {
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/resources/js-react'],
  testMatch: ['**/__tests__/**/*.integration.test.js', '**/__tests__/**/*.integration.test.jsx'],
  moduleFileExtensions: ['js', 'jsx'],
  transform: {
    '^.+\\.(js|jsx)$': ['babel-jest', {
      presets: [
        ['@babel/preset-env', { targets: { node: 'current' } }],
        ['@babel/preset-react', { runtime: 'automatic' }]
      ]
    }]
  },
  moduleNameMapper: {
    '\\.(css|less|scss|sass)$': '<rootDir>/resources/js-react/__tests__/__mocks__/styleMock.js',
  },
  setupFilesAfterEnv: ['<rootDir>/resources/js-react/__tests__/setup.js'],
  collectCoverageFrom: [
    'resources/js-react/**/*.{js,jsx}',
    '!resources/js-react/**/*.test.{js,jsx}',
    '!resources/js-react/__tests__/**',
    '!resources/js-react/config/**',
  ],
  testTimeout: 10000,
};
