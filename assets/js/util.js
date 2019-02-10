import BigNumber from 'bignumber.js';
import _ from 'lodash';

export function asXmr(value, digits)
{
    if (digits == null)
        digits = 8;

    BigNumber.config({
        DECIMAL_PLACES: digits,
        EXPONENTIAL_AT: 13
    });
    var intValue = new BigNumber(value, 10);
    var divisor = new BigNumber(10).pow(12);
    return intValue.dividedBy(divisor).toFormat(digits);
}

export function setContentMinHeight(contentElement)
{
    if (document.getElementById('navbar') == null)
    {
        return;
    }

    let headerHeight = document.getElementById('navbar').offsetHeight;
    let footerHeight = document.getElementById('page-footer').offsetHeight;
    let contentMinHeight = window.innerHeight - headerHeight - footerHeight;
    contentElement.style.minHeight = contentMinHeight + 'px';
}

export function setContentMinHeightDynamically(contentElement)
{
    setContentMinHeight(contentElement);
    window.addEventListener('resize', () => { setContentMinHeight(contentElement) });
}

export function getWidth()
{
    let body = document.body;
    return window.getComputedStyle(body).getPropertyValue('width').replace(/px/g, '');
}

export function getRandomInt(min, max)
{
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

export function addParachutes(container, imgUrl, options)
{
    var defaultOptions = {
        parachuteClassName: 'parachute',
        leftRange: [1, 100],
        maxHeightRange: [10, 50],
        animationDurationRange: [10, 20],
        animationDelayRange: [1, 5]
    }

    options = _.merge({}, defaultOptions, options);

    var parachutes = container.getElementsByClassName(options.parachuteClassName);

    while (parachutes[0]) {
        parachutes[0].parentNode.removeChild(parachutes[0]);
    }

    var parachutesQuantity = container.clientWidth / 55;
    for (var i = 0; i < parachutesQuantity; i++)
    {
        var parachute = document.createElement('img');
        parachute.src = document.getElementById('parachute-url').value;
        parachute.className = options.parachuteClassName;
        parachute.style.left = getRandomInt(
            options.leftRange[0], options.leftRange[1]
        ) + '%';
        parachute.style.maxHeight = getRandomInt(
            options.maxHeightRange[0], options.maxHeightRange[1]
        ) + '%';
        parachute.style.animationDuration = getRandomInt(
            options.animationDurationRange[0], options.animationDurationRange[1]
        ) + 's';
        parachute.style.animationDelay = getRandomInt(
            options.animationDelayRange[0], options.animationDelayRange[1]
        ) + 's';
        container.insertBefore(parachute, container.children[0]);
    }
}

export function addParachutesResponsive(container, imgUrl, options)
{
    addParachutes(container, imgUrl, options);
    window.addEventListener('resize', () => { addParachutes(container, imgUrl, options); });
}

export function sortRecords(records, key, order, isAlpha = false) {
    if (!isAlpha)
        return _.orderBy(records, key, order);
    else
    {
        let  output = records.sort((a,b) => a[key].localeCompare(b[key], undefined, {numeric: true}));
        return order === "asc" ? output : _.reverse(output);
    }
}

export function isValidEmail(address) {
    let regex = /^\S+@\S+$/
    return regex.test(address);
}