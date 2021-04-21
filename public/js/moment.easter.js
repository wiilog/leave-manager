moment.easter = function Easter20ops(year) {
    /*jslint bitwise: true, vars: true */
    var a = (year / 100 | 0) * 1483 - (year / 400 | 0) * 2225 + 2613;
    var b = ((year % 19 * 3510 + (a / 25 | 0) * 319) / 330 | 0) % 29;
    var c = 148 - b - ((year * 5 / 4 | 0) + a - b) % 7;

    return moment({year: year, month: (c / 31 | 0) - 1, day: c % 31 + 1});
};

moment.fn.easter = function () {
    return moment.easter(this.year());
};

if (typeof module !== 'undefined') {
    module.exports = moment;
} else {
    this.moment = moment;
}
