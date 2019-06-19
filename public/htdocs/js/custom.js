/**
 * javascript custom Utils
 *
 *
 */

/**
 * show alertbiz_node_name
 *
 * @param type
 * @param time
 * @param msg
 */
const showSmallBox = function (type, time, msg) {

    type = type || "";
    let timeout = time || 5000;
    let message = msg || "";
    let dict = flashMessageJsonObj || {};
    let opt;

    console.log(type);

    switch (type) {
        case "success" :
            opt = {
                title: dict.success,
                content: "<i class='fa fa-check-circle'></i> <i>" + dict.success_content + "</i>",
                color: "#739e73",
                iconSmall: "fa fa-check fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        case "success_landing" :
            opt = {
                title: dict.success,
                content: "<i class='fa fa-check-circle'></i> <i>Success!!</i>",
                color: "#739e73",
                iconSmall: "fa fa-check fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        case "success_message" :
            opt = {
                title: dict.success,
                content: "<i class='fa fa-check-circle'></i> <i>" + message + "</i>",
                color: "#739e73",
                iconSmall: "fa fa-check fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        case "error" :
            opt = {
                title: dict.error_title,
                content: "<i class='fa fa-warning'></i> <i>A temporary error has occurred. Please try again later...</i>",
                color: "#c46a69",
                iconSmall: "fa fa-ban fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        case "error_message" :
            opt = {
                title: dict.error_title,
                content: "<i class='fa fa-warning'></i> <i>" + message + "</i>",
                color: "#c46a69",
                iconSmall: "fa fa-ban fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        case "login_fail" :
            opt = {
                title: dict.error_title,
                content: "<i class='fa fa-warning'></i> <i>Fail to login. Plaese Check your email or password</i>",
                color: "#c46a69",
                iconSmall: "fa fa-ban fa-2x fadeInRight animated",
                timeout: timeout
            };
            break;
        default :
            opt = {
                title: dict.progress_title,
                content: "<i class='fa fa-clock-o'></i> <i>" + dict.progress_content + "</i>",
                color: "#3276b1",
                iconSmall: "fa fa-gear fa-2x fa-spin fadeInRight",
                timeout: timeout
            };
    }

    $.smallBox(opt);
};

/**
 *
 * @param type
 * @param jsonData
 * @param time
 */
const showBigBox = function (type, jsonData, time) {

    type = type || "";
    let opt = jsonData;
    let timeout = time || null;

    switch (type) {
        default :
            opt = {
                title: "Success",
                content: jsonData.message,
                color: "#739E73",
                timeout: timeout,
                icon: "fa fa-check swing animated",
                number: jsonData.number
            };
    }

    $.bigBox(opt);
};

/**
 * Generate Random Key
 *
 * @param lenth
 * @returns {string}
 */
const makeRandomKey = function (lenth) {

    let text = "";
    let textLen = lenth || 10;

    let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (let i = 0; i < textLen; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
};

/**
 * ajax request handling
 *
 * @returns {{call: call}}
 */
const $_Ajax = function () {
    return {
        call: function (triggerObj, url, params, method, opt, bsCallback, successCallback, failCallback, completeCallback) {

            bsCallback = bsCallback || null;
            successCallback = successCallback || null;
            failCallback = failCallback || null;
            completeCallback = completeCallback || null;
            method = method || "POST";
            opt = opt || {
                "after_move_type": "reload",
                "after_move_url": "",
                "after_unload_release": "true"
            };

            $.ajax({
                url: url,
                type: method,
                data: params,
                dataType: "json",
                beforeSend: (bsCallback != null) ? bsCallback : function () {
                    if (!!triggerObj) triggerObj.button("loading");
                    showSmallBox();
                },
                success: (successCallback != null) ? successCallback : function (res) {

                    if (res.result === "session_expired") {
                        showSmallBox("error_message", 1000, res.data.message);
                        readyState = null;
                        location.href = "/";
                        return;
                    }

                    if (res.result === "success") {
                        if (!!console) console.log("success", res);

                        if (opt.after_unload_release === "true") {
                            readyState = null;
                        }

                        switch (opt.after_move_type) {
                            case "reload" :
                                showSmallBox("success");
                                location.reload();
                                break;
                            case "href" :
                                showSmallBox("success");
                                location.href = opt.after_move_url;
                                break;
                            default :
                                showSmallBox("success_landing");
                                return res;
                        }

                        return;
                    }

                    if (!!console) console.log("error", res);
                    this.showErrorMessage(res);

                },
                error: (failCallback != null) ? failCallback : function (res) {
                    if (!!console) console.log("server-error", res);
                    this.showErrorMessage(res);
                },
                complete: (completeCallback != null) ? completeCallback : function () {
                    if (!!triggerObj) triggerObj.button("reset");
                },
                showErrorMessage: function (res) {
                    if (res.message != "" && res.message != undefined) {
                        showSmallBox("error_message", 5000, res.message);
                    } else {
                        showSmallBox("error");
                    }
                }
            });
        }
    };
};

/**
 * 파일 업로드 포함일경우
 *
 * @returns {{call: call}}
 */
const $_AjaxForm = function () {
    return {
        call: function (triggerObj, form, url, method, bsCallback, successCallback, failCallback, completeCallback) {

            const $_form = form;
            bsCallback = bsCallback || null;
            successCallback = successCallback || null;
            failCallback = failCallback || null;
            completeCallback = completeCallback || null;
            method = method || "POST";

            $_form.attr("action", url).ajaxForm({
                beforeSubmit: (bsCallback != null) ? bsCallback : function () {
                    if (!!triggerObj) triggerObj.button("loading");
                    showSmallBox();
                },
                success: (successCallback != null) ? successCallback : function (res) {
                    if (res.result === "success") {
                        if (!!console) console.log("success", res);
                        showSmallBox("success");
                        location.reload();
                        return;
                    }

                    if (!!console) console.log("error", res);
                    this.showErrorMessage(res);

                },
                error: (failCallback != null) ? failCallback : function (res) {
                    if (!!console) console.log("error", res);
                    this.showErrorMessage(res);
                },
                complete: (completeCallback != null) ? completeCallback : function (res) {
                    if (!!triggerObj) triggerObj.button("reset");
                },
                showErrorMessage: function (res) {
                    if (res.message != "" && res.message != undefined) {
                        showSmallBox("error_message", 5000, res.message);
                    } else {
                        showSmallBox("error");
                    }
                }
            });

            $_form.submit();
        }
    };
};

$.fn.serializeArrayWithCheckboxes = function () {
    let rCRLF = /\r?\n/g;
    return this.map(function () {
        return this.elements ? jQuery.makeArray(this.elements) : this;
    }).map(function (i, elem) {
        let val = $(this).val();
        if (this.name !== "") {
            if (val === null) {
                return val == null;
                //next 2 lines of code look if it is a checkbox and set the value to blank
                //if it is unchecked
            } else if (this.type === "checkbox" || this.type === "radio") {
                if (this.checked === true) {
                    return {name: this.name, value: this.value};
                } else {
                    if (this.type === "checkbox") {
                        return {name: this.name, value: false};
                    }
                }
                //next lines are kept from default jQuery implementation and
                //default to all checkboxes = on
            } else {
                return $.isArray(val) ?
                    $.map(val, function (val, i) {
                        return {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                    }) :
                    {name: elem.name, value: val.replace(rCRLF, "\r\n")};
            }
        }
    }).get();
};

/**
 * form data
 * @param formObject
 * @returns {{}}
 */
const $_FormDataSerialize = function (formObject) {
    let params = {};
    if (formObject) {
        $.each(formObject.serializeArrayWithCheckboxes(), function (i, obj) {
            if (obj.name !== undefined && obj.name !== "") {
                if (obj.name.indexOf("[]") >= 0) {
                    if ($.isArray(params[obj.name]) === false) {
                        params[obj.name] = new Array();
                    }
                    params[obj.name].push(obj.value);
                } else {
                    params[obj.name] = obj.value;
                }
            }
        });
    }
    return params;
};

/**
 * form check requ
 * @param formObject
 * @returns {boolean}
 */
const $_FormCheckRequired = function (formObject) {

    let validate = true;
    let errorTitle;
    let dict = flashMessageJsonObj || {};

    if (!!formObject) {
        formObject.find(":checkbox, :text, :password, select, input[type='number'], input[type='tel']").each(function () {

            $(this).parent("label").removeClass("state-error");
            $(".note-error").remove();
            if ($(this).attr("disabled") != "disabled" && !!$(this).attr("required")) {

                if ($(this).attr("type") == "checkbox" && $(this).is(":checked") === false) {
                    $(this).parent("label").addClass("state-error");
                    validate = false;
                    return false;  // quit each
                } else if ($(this).val() == "") {
                    errorTitle = (!!$(this).attr("data-require-title")) ? $(this).attr("data-require-title") : dict.require_title;
                    $(this).parent("label").addClass("state-error").append("<div class=\"note note-error text-left\">" + errorTitle + "</div>");
                    validate = false;
                    return false; // quit each
                }

                if ($(this).attr("name") === "req_key[]" || $(this).attr("name") === "res_key[]") {
                    if (!checkValidKey($(this).val())) {
                        $(this).parent("label").addClass("state-error").append("<div class=\"note note-error text-left\">" + dict.invalid_title + "</div>");
                        validate = false;
                        return false; // quit each
                    }
                }

                $(this).parent("label").removeClass("state-error");
            }
        });
    }

    return validate;
};


const checkValidKey = function (txt) {
    let idReg = /^[A-Za-z_][A-Za-z0-9_]*$/g;
    return idReg.test(txt);
};

/**
 * 클립보드로 복사
 * @param element
 * @param obj
 */
const copyToClipboard = function (element, obj) {

    let success = false;
    let $temp = $("<input>");
    $temp.attr("id", "copytext");
    let message = $(obj).data("message-title");
    $("body").append($temp);
    //$(obj).parent().append($temp);
    $temp.val($(element).text()).focus().select();

    try {
        success = document.execCommand("copy");
        if (!!console) console.log("copy result :: ", success);
    } catch (e) {
        if (!!console) console.log("error", e.message);
    }

    $temp.remove();

    if (!!success) {
        showSmallBox("success_message", 2000, message);
    }
};

/**
 * 데이터 테이블 그리기
 *
 * @param tableObjName
 * @param variable
 * @param order
 * @param lengthMenu
 * @param addOption
 */
const setDataTable = function (tableObjName, variable, order, lengthMenu, addOption) {

    let breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };

    let orderObj = order || {"order": [[0, "desc"]]};
    let lengthMenuObj = lengthMenu || {"lengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]};

    let $_tableObj = $("#" + tableObjName);
    let dTableOpt = {
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>" +
            "t" +
            "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth": true,
        "oLanguage": {
            "sSearch": "<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-search\"></i></span>"
        },
        "preDrawCallback": function () {
            if (!variable) {
                variable = new ResponsiveDatatablesHelper($_tableObj, breakpointDefinition);
            }
        },
        "rowCallback": function (nRow) {
            variable.createExpandIcon(nRow);
        },
        "drawCallback": function (oSettings) {
            variable.respond();
            // colspan 이 안맞게 나와서 추가로 처리 (추가수정 이슈 있음)
            $_tableObj.find(".dataTables_empty").attr("colspan", $_tableObj.find("th").length);
        }
    };

    $.extend(dTableOpt, orderObj, lengthMenuObj);

    if (!!addOption) {
        $.extend(dTableOpt, addOption);
    }
    //if (!!console.log) console.log("dTable", dTableOpt);
    $_tableObj.dataTable(dTableOpt);
};

const prevUploadImage = function (e, targetImage) {

    let files = e.target.files;
    let filesArr = Array.prototype.slice.call(files);
    let result = true;

    filesArr.forEach(function (f) {

        if (!f.type.match("image.*")) {
            targetImage.attr("src", "").hide();
            result = false;
            return false;
        }

        let reader = new FileReader();
        reader.onload = function (e) {
            targetImage.attr("src", e.target.result).show();
        };

        reader.readAsDataURL(f);

    });

    return result;
};

const sleep = function (time) {
    return new Promise((resolve) => setTimeout(resolve, time));
};

const testBizUnit = function ($_this, targetUrl, submitData) {

    new $_Ajax().call($_this, '/console/apps/bunit/getBizParams', submitData, "POST", {},

        function () {
            showSmallBox();
            $(".btn-test-close").button("loading");
        },
        function (res) {
            var $_modal = $('#modal_operation_test');
            $_modal.find('.req-data-frm-field').html('');
            $_modal.find('#test-result').addClass('hide');


            $.each(res.data, function () {
                var $_cl = $_modal.find('.clone-field').clone();
                $_cl.find('.key').text(this.parameter_key_name);
                $_cl.find('input[type="text"]').attr('name', this.parameter_key_name);
                $_modal.find('.req-data-frm-field').append($_cl.html());
            });
            $_modal.modal().find('form').off('submit').on('submit', function (e) {
                e.preventDefault();
                $(this).find('input[type="text"]').each(function () {
                    submitData[$(this).attr('name')] = $(this).val();
                });
                new $_Ajax().call($_this, targetUrl, submitData, "POST", {},

                    function () {
                        showSmallBox();
                        $(".btn-test-close").button("loading");
                    },
                    function (res) {

                        // console.log(res);
                        if (res.result === "session_expired") {
                            showSmallBox("error_message", 1000, res.data.message);
                            readyState = null;
                            location.href = "/";
                            return;
                        }

                        if (res.result !== "success") {
                            showSmallBox("error_message", 3000, res.data.message);
                            return;
                        }


                        $("#biz_test_result").html("");

                        let resultjson = res.data.res.result.data;
                        let resLen = resultjson.length;
                        //let resultString = "[\r";
                        let resultString = [];
                        let originData = '';
                        $_modal.find('#test-result').removeClass('hide');

                        $("#biz_test_result").append("--- start ---\r[\r");

                        $.each(resultjson, function (key, ops) {

                            resultString[key] = "";
                            resultString[key] += "  {\r";
                            resultString[key] += "      \"op_name\": \"" + ops.op_name + "\",\r";
                            resultString[key] += "      \"request_target_url\": \"" + ops.request_target_url + "\",\r";
                            resultString[key] += "      \"server_status\": \"" + ops.server_status + "\",\r";

                            // 빠짐
                            // resultString[key] += "      \"request_method\": \"" + ops.request_method + "\",\r";

                            resultString[key] += "      \"request\": {\r";
                            $.each(ops.request, function (reqKey, reqVal) {
                                reqVal = (reqVal == null) ? null : "\"" + reqVal + "\"";
                                resultString[key] += "        \"" + reqKey + "\": " + reqVal + ",\r";
                            });
                            resultString[key] += "      },\r";

                            resultString[key] += "      \"response\": {\r";
                            $.each(ops.response, function (resKey, resVal) {
                                console.log("typeof resVal", typeof resVal);
                                resVal = (resVal == null) ? null : " " + (typeof resVal === "object" ? JSON.stringify(resVal) : "\"" + resVal + "\"");
                                resultString[key] += "        \"" + resKey + "\": " + resVal + ",\r";
                            });
                            resultString[key] += "      },\r";

                            resultString[key] += "      \"origin_response\": {\r";
                            //console.log("ops.origin_response :: ", ops.origin_response);
                            //$.each(ops.origin_response, function (key, row) {
                            // switch (ops.origin_response.res_type) {
                            //     case "XML" :
                            //         originData = $.parseXML(ops.origin_response.res_data);
                            //         originData = (new XMLSerializer()).serializeToString(originData);
                            //         break;
                            //     case "JSON" :
                            //         originData = JSON.stringify(ops.origin_response.res_data);
                            //         break;
                            // }

                            originData = JSON.stringify(ops.response);
                            //console.log("row.res_data :: ", ops.origin_response.res_data);
                            resultString[key] += "        " + originData + ",\r";
                            //});
                            resultString[key] += "      },\r";

                            //resultString[key] += "      \"origin_response\": \"" + ops.origin_response + "\"\r";
                            resultString[key] += "  }" + ((parseInt(key) < (resLen - 1)) ? "," : "") + "\r";

                            $("#biz_test_result").delay(800).queue(function (next) {
                                $(this).append(resultString[key]);
                                $("pre code").each(function (i, block) {
                                    hljs.highlightBlock(block);
                                });
                                next();
                            });
                        });

                        //resultString[key] += "]";
                        $("#biz_test_result").delay(200).queue(function (next) {
                            $("#biz_test_result").append("]\r--- end ---");
                            showSmallBox("success_landing");
                            $(".btn-test-close").button("reset");
                            next();
                        });
                    }
                    , function () {
                        showSmallBox('error');
                    }
                ); // end of ajax call


            });
        });

    return false;


}