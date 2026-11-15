const fs = require('node:fs');
const path = require('node:path');
const crypto = require('node:crypto');
const vm = require('node:vm');

const root = process.env.WAPPOINTMENT_LEGACY_ROOT;
if (!root) throw new Error('Set WAPPOINTMENT_LEGACY_ROOT to the isolated audited master checkout.');

function read(relative, hash) {
    const source = fs.readFileSync(path.join(root, relative), 'utf8');
    if (crypto.createHash('sha256').update(source).digest('hex') !== hash) {
        throw new Error(`Legacy source does not match audited master: ${relative}`);
    }
    return source;
}

function component(relative, hash) {
    const script = read(relative, hash).match(/<script>([\s\S]*?)<\/script>/)[1]
        .replace(/^import .*$/gm, '').replace('export default', 'module.exports =');
    const context = {
        module: { exports: {} }, Dates: {}, DatesExtended: {}, IncrementTime: {},
        CalendarMonth: {}, CalendarWeek: {}, BookingButton: {},
    };
    vm.runInNewContext(script, context, { timeout: 1000 });
    return context.module.exports;
}

const dates = component('resources/js/Modules/DatesExtended.vue', '148eb77b79157ccf8af08d6bb4b7008d86be62e53fe0db7e213404d21e9e0743');
const calendar = component('resources/js/BookingForm/Calendar.vue', 'e5a5a47f80c56dce7b46335e44f4de8ee18f3827e19cc1eaab7bd94931012d51');
const daySlots = component('resources/js/BookingForm/DaySlots.vue', '9591eb679dbbb8ba0600df2ca66b6f6487183f0fdce25594282f9dbb0e1f74b7');
const increment = component('resources/js/BookingForm/IncrementTime.vue', '53e8417161cc015f02ac8eaafaed06c0be4c63d04afb6bfd6055c28fa546ed38');

class Moment {
    constructor(seconds) { this.seconds = seconds; }
    clone() { return new Moment(this.seconds); }
    add(value, unit) {
        this.seconds += value * (unit === 'hours' || unit === 'h' ? 3600 : 60);
        return this;
    }
    startOf(unit) {
        if (unit === 'hour') this.seconds = Math.floor(this.seconds / 3600) * 3600;
        return this;
    }
    set(unit, value) {
        if (unit === 'second') this.seconds = Math.floor(this.seconds / 60) * 60 + value;
        return this;
    }
    unix() { return Math.floor(this.seconds); }
}

const momenttz = function () { return new Moment(0); };
momenttz.tz = (input) => new Moment(input instanceof Moment ? input.seconds : input);
const weekly = JSON.parse(fs.readFileSync(path.join(__dirname, 'legacy-weekly.json'), 'utf8'));
const windowsFile = process.env.WAPPOINTMENT_NOTICE_WINDOWS || path.join(__dirname, 'legacy-notice-horizon.json');
const windows = JSON.parse(fs.readFileSync(windowsFile, 'utf8'));
const casesSource = fs.readFileSync(path.join(__dirname, 'notice-slot-cases.json'));
const scenarios = JSON.parse(casesSource);
const result = {};

for (const [name, captured] of Object.entries(windows.cases)) {
    result[name] = {};
    for (const [scenario, input] of Object.entries(scenarios)) {
        const starts = [];
        const viewData = {
            ...captured.settings,
            more_st: input.more_st,
            starting_each: input.starting_each,
            buffer_time: input.buffer,
            min_bookable: captured.settings.hours_before_booking_allowed,
        };
        const context = {
            viewData,
            timeprops: { currentTz: captured.timezone },
            now: new Moment(captured.now),
            duration: input.duration,
            intervals: captured.windows.map(([start, end]) => ({ start, end })),
            getMomentObject: () => momenttz,
            getDecimalPart: dates.methods.getDecimalPart,
            currentTime: () => captured.now,
            identifySlot: ({ start }) => starts.push(start),
        };
        context.minTodayHour = dates.computed.minTodayHour.call(context);
        context.minTodayMin = dates.computed.minTodayMin.call(context);
        context.getMinStart = () => dates.methods.getMinStart.call(context);
        context.incrementType = increment.computed.incrementType.call(context);
        context.incrementTime = () => increment.methods.incrementTime.call(context);
        const newStart = context.getMinStart().unix();
        const intervals = { intervals: context.intervals.map((interval) => ({ ...interval })) };
        if (intervals.intervals.length > 0) {
            let visible = calendar.methods.cleanStartingFromEnd.call(context, intervals, newStart);
            visible = calendar.methods.incrementStartingValue.call(context, visible, newStart);
            visible = calendar.methods.cleanStartingFromEnd.call(context, visible, newStart);
            daySlots.methods.convertIntervalsToSlots.call({
                intervals: visible.intervals,
                duration: (input.duration + input.buffer) * 60,
                viewData,
                identifySlot: ({ start }) => starts.push(start),
                incrementType: daySlots.methods.incrementType,
            });
        }
        result[name][scenario] = starts;
    }
}
console.log(JSON.stringify({
    source: windows.source,
    source_sha256: {
        'DatesExtended.vue': '148eb77b79157ccf8af08d6bb4b7008d86be62e53fe0db7e213404d21e9e0743',
        'Calendar.vue': 'e5a5a47f80c56dce7b46335e44f4de8ee18f3827e19cc1eaab7bd94931012d51',
        'DaySlots.vue': '9591eb679dbbb8ba0600df2ca66b6f6487183f0fdce25594282f9dbb0e1f74b7',
        'IncrementTime.vue': '53e8417161cc015f02ac8eaafaed06c0be4c63d04afb6bfd6055c28fa546ed38',
    },
    cases_sha256: crypto.createHash('sha256').update(fs.readFileSync(path.join(__dirname, 'notice-horizon-cases.php'))).digest('hex'),
    windows_sha256: crypto.createHash('sha256').update(fs.readFileSync(windowsFile)).digest('hex'),
    scenarios_sha256: crypto.createHash('sha256').update(casesSource).digest('hex'),
    scope: 'Original master Calendar/DatesExtended/IncrementTime/DaySlots methods executed in UTC with frozen clock and a minimal Moment-compatible shim; captured PHP weekly windows; no Vue mount or WordPress.',
    slots: result,
}, null, 2));
