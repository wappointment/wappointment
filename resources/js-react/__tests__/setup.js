import '@testing-library/jest-dom';

const nodeGlobals = require('node:vm').runInThisContext('globalThis');
const { TextDecoder, TextEncoder } = require('node:util');
const { ReadableStream, TransformStream, WritableStream } = require('node:stream/web');
const { BroadcastChannel } = require('node:worker_threads');
global.fetch ||= nodeGlobals.fetch;
global.Headers ||= nodeGlobals.Headers;
global.Request ||= nodeGlobals.Request;
global.Response ||= nodeGlobals.Response;
global.TextDecoder ||= TextDecoder;
global.TextEncoder ||= TextEncoder;
global.ReadableStream ||= ReadableStream;
global.TransformStream ||= TransformStream;
global.WritableStream ||= WritableStream;
global.BroadcastChannel ||= BroadcastChannel;

const { server } = require('./mocks/server');

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
