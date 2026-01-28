import '@testing-library/jest-dom';
import { server } from './mocks/server';

// Mock WordPress global object
global.wp = {
  i18n: {
    __: (text) => text,
    _x: (text) => text,
    _n: (single, plural, number) => number === 1 ? single : plural,
  },
};

// Setup MSW server
beforeAll(() => server.listen({ onUnhandledRequest: 'error' }));
afterEach(() => server.resetHandlers());
afterAll(() => server.close());
