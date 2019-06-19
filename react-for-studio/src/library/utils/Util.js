import _ from 'lodash';

import stores from '../../stores';

/* global global_data, $ */
export default class Util {
    static confirmMessage(
        title,
        content,
        btns = '[No][Yes]',
        positive = 'Yes'
    ) {
        return new Promise((resolve, reject) => {
            $.SmartMessageBox(
                {
                    title: title,
                    content: content,
                    buttons: btns,
                },
                ButtonPressed => {
                    if (ButtonPressed === positive) {
                        resolve();
                    } else {
                        reject();
                    }
                }
            );
        });
    }
    static alertMessage(title, content) {
        return new Promise(resolve => {
            $.SmartMessageBox(
                {
                    title: title,
                    content: content,
                    buttons: '[Ok]',
                },
                () => {
                    resolve();
                }
            );
        });
    }
    static formCheckRequired(f) {
        const checkValidKey = txt => {
            const idReg = /^[A-Za-z_][A-Za-z0-9_]*$/g;
            return idReg.test(txt);
        };
        let validate = true;
        let errorTitle;
        let dict = global_data.dictionary.flash_message || {};
        const formObject = $(f);

        if (!!formObject) {
            formObject
                .find(
                    ":checkbox, :text, :password, select, input[type='number'], input[type='tel']"
                )
                .each(function() {
                    $(this)
                        .parent('label')
                        .removeClass('state-error');
                    $('.note-error').remove();
                    if (
                        $(this).attr('disabled') !== 'disabled' &&
                        !!$(this).attr('required')
                    ) {
                        if (
                            $(this).attr('type') === 'checkbox' &&
                            $(this).is(':checked') === false
                        ) {
                            $(this)
                                .parent('label')
                                .addClass('state-error');
                            validate = false;
                            return false; // quit each
                        } else if ($(this).val() === '') {
                            errorTitle = !!$(this).attr('data-require-title')
                                ? $(this).attr('data-require-title')
                                : dict.require_title;
                            $(this)
                                .parent('label')
                                .addClass('state-error')
                                .append(
                                    `<div class="note note-error text-left">${errorTitle}</div>`
                                );
                            validate = false;
                            return false; // quit each
                        }

                        if ($(this).attr('class') === 'input-key') {
                            if (!checkValidKey($(this).val())) {
                                $(this)
                                    .parent('label')
                                    .addClass('state-error')
                                    .append(
                                        `<div class="note note-error text-left">${
                                            dict.invalid_title
                                        }</div>`
                                    );
                                validate = false;
                                return false; // quit each
                            }
                        }
                        $(this)
                            .parent('label')
                            .removeClass('state-error');
                    }
                });
        }

        return validate;
    }
    static showSmallBox(type, time, msg) {
        type = type || '';
        let timeout = time || 1000;
        let message = msg || '';
        let dict = global_data.dictionary.flash_message || {};
        let opt;
        switch (type) {
            case 'success':
                opt = {
                    title: dict.success,
                    content: `<i class='fa fa-check-circle'></i> <i>${
                        dict.success_content
                    }</i>`,

                    color: '#739e73',
                    iconSmall: 'fa fa-check fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            case 'success_landing':
                opt = {
                    title: dict.success,
                    content:
                        "<i class='fa fa-check-circle'></i> <i>Success!!</i>",
                    color: '#739e73',
                    iconSmall: 'fa fa-check fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            case 'success_message':
                opt = {
                    title: dict.success,
                    content: `<i class='fa fa-check-circle'></i> <i>${message}</i>`,
                    color: '#739e73',
                    iconSmall: 'fa fa-check fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            case 'error':
                opt = {
                    title: dict.error_title,
                    content:
                        "<i class='fa fa-warning'></i> <i>A temporary error has occurred. Please try again later...</i>",
                    color: '#c46a69',
                    iconSmall: 'fa fa-ban fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            case 'error_message':
                opt = {
                    title: dict.error_title,
                    content: `<i class='fa fa-warning'></i> <i>${message}</i>`,
                    color: '#c46a69',
                    // iconSmall: 'fa fa-ban fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            case 'login_fail':
                opt = {
                    title: dict.error_title,
                    content:
                        "<i class='fa fa-warning'></i> <i>Fail to login. Plaese Check your email or password</i>",
                    color: '#c46a69',
                    iconSmall: 'fa fa-ban fa-2x fadeInRight animated',
                    timeout: timeout,
                };
                break;
            default:
                opt = {
                    title: dict.progress_title,
                    content: `<i class='fa fa-clock-o'></i> <i>${
                        dict.progress_content
                    }</i>`,

                    color: '#3276b1',
                    iconSmall: 'fa fa-gear fa-2x fa-spin fadeInRight',
                    timeout: timeout,
                };
        }

        $.smallBox(opt);
    }
    static signOut() {
        stores.userInfoStore.signOut();
    }
    static parseCsvMethod(data, delimeter = ',') {
        let rowArr = data.split('\n');
        _.pullAt(rowArr, [0]);

        return rowArr.map(arrs => {
            let arr = arrs.split(delimeter);
            return {
                key: arr[0],
                var_type: arr[1],
                desc: arr[2],
                required_flag: arr[3],
            };
        });
    }
    static checkJson(str) {
        return /^[\],:{}\s]*$/.test(
            str
                .replace(/\\["\\\/bfnrtu]/g, '@')
                .replace(
                    /"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
                    ']'
                )
                .replace(/(?:^|:|,)(?:\s*\[)+/g, '')
        );
    }
}
