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
  transformIgnorePatterns: ['/node_modules/(?!(until-async)/)'],
  moduleNameMapper: {
    '^msw/node$': '<rootDir>/node_modules/msw/lib/node/index.js',
    '^@mswjs/interceptors/WebSocket$': '<rootDir>/node_modules/@mswjs/interceptors/lib/browser/interceptors/WebSocket/index.js',
    '^@mswjs/interceptors/(.*)$': '<rootDir>/node_modules/@mswjs/interceptors/lib/node/interceptors/$1/index.js',
    '^@mswjs/interceptors$': '<rootDir>/node_modules/@mswjs/interceptors/lib/node/index.js',
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
