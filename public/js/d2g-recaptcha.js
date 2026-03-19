var captchaCodeRegistration = '';
var captchaCodeLogin = '';
var captchaCodeWalkin = '';
var captchaCodeEmail = '';
var captchaCodeCalendar = '';

function correctCaptchaRegistration(response) {
    captchaCodeRegistration = response;
}
function correctCaptchaLogin(response) {
    captchaCodeLogin = response;
}
function correctCaptchaWalkin(response) {
    captchaCodeWalkin = response;
}
function correctCaptchaEmail(response) {
    captchaCodeEmail = response;
}
function correctCaptchaCalendar(response) {
    captchaCodeCalendar = response;
}

function d2gOnloadCallback() {

    if (typeof grecaptcha === 'undefined') {
        return;
    }

    // Registration captcha
    if (document.getElementById(d2gRecaptchaVars.elementIdRegistration)) {
        grecaptcha.render(d2gRecaptchaVars.elementIdRegistration, {
            sitekey: d2gRecaptchaVars.siteKey,
            callback: correctCaptchaRegistration
        });
    }

    // Login captcha
    if (document.getElementById(d2gRecaptchaVars.elementIdLogin)) {
        grecaptcha.render(d2gRecaptchaVars.elementIdLogin, {
            sitekey: d2gRecaptchaVars.siteKey,
            callback: correctCaptchaLogin
        });
    }

    // Walk-in captcha
    if (document.getElementById(d2gRecaptchaVars.elementIdWalkin)) {
        grecaptcha.render(d2gRecaptchaVars.elementIdWalkin, {
            sitekey: d2gRecaptchaVars.siteKey,
            callback: correctCaptchaWalkin
        });
    }

    // Email captcha
    if (document.getElementById(d2gRecaptchaVars.elementIdEmail)) {
        grecaptcha.render(d2gRecaptchaVars.elementIdEmail, {
            sitekey: d2gRecaptchaVars.siteKey,
            callback: correctCaptchaEmail
        });
    }

    // Calendar captcha
    if (document.getElementById(d2gRecaptchaVars.elementIdCalendar)) {
        grecaptcha.render(d2gRecaptchaVars.elementIdCalendar, {
            sitekey: d2gRecaptchaVars.siteKey,
            callback: correctCaptchaCalendar
        });
    }
}