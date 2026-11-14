const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const vm = require('node:vm');

const root = process.env.WAPPOINTMENT_LEGACY_ROOT;
if (!root) throw new Error('Set WAPPOINTMENT_LEGACY_ROOT to the isolated audited master checkout.');
const source = fs.readFileSync(path.join(root, 'resources/js/BookingForm/DaySlots.vue'), 'utf8');
if (crypto.createHash('sha256').update(source).digest('hex') !== '9591eb679dbbb8ba0600df2ca66b6f6487183f0fdce25594282f9dbb0e1f74b7') {
    throw new Error('Legacy DaySlots source does not match audited master.');
}
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1]
    .replace(/^import .*$/gm, '').replace('export default', 'module.exports =');
const context = { module: { exports: {} }, Dates: {}, BookingButton: {} };
vm.runInNewContext(script, context, { timeout: 1000 });
const legacy = context.module.exports.methods;
const windows = JSON.parse(fs.readFileSync(path.join(__dirname, 'legacy-weekly.json'), 'utf8'));
const casesSource = fs.readFileSync(path.join(__dirname, 'slot-cases.json'));
const cases = JSON.parse(casesSource);
const slots = {};
for (const [name, intervals] of Object.entries(windows.windows)) {
    slots[name] = {};
    for (const [scenario, input] of Object.entries(cases)) {
        const starts = [];
        legacy.convertIntervalsToSlots.call({
            intervals: intervals.map(([start, end]) => ({ start, end })),
            duration: (input.duration + input.buffer) * 60,
            viewData: input.settings,
            incrementType: legacy.incrementType,
            identifySlot: ({ start }) => starts.push(start),
        });
        slots[name][scenario] = starts;
    }
}
console.log(JSON.stringify({
    source: windows.source,
    scope: 'Unmodified DaySlots conversion/increment methods over captured PHP intervals; no Vue mount, browser clock, HTTP or lifecycle execution.',
    weekly_sha256: crypto.createHash('sha256').update(fs.readFileSync(path.join(__dirname, 'legacy-weekly.json'))).digest('hex'),
    cases_sha256: crypto.createHash('sha256').update(casesSource).digest('hex'),
    slots,
}, null, 2));
