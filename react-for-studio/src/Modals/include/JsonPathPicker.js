import React, { Component } from 'react';

export default class JsonPathPicker extends Component {
    constructor(props) {
        super(props);
        this.state = {
            choosen: null,
        };
    }
    componentWillReceiveProps(nextp) {
        if (nextp.json !== this.props.json) {
            // string compare
            this.setState({
                choosen: null, // reset choosen
            });
        }
        if (nextp.path !== undefined) {
            let nextPath = null;
            if (!nextp.path) {
                // '' | null
                nextPath = nextp.path;
            } else {
                nextPath = nextp.path.replace(/\./g, ' .');
                nextPath = nextPath.replace(/\[/g, ' [');
            }
            this.setState({
                choosen: nextPath,
            });
        }
    }
    shouldComponentUpdate(nextp, nexts) {
        if (nextp.json !== this.props.json) {
            return true;
        } else if (nexts.choosen !== this.state.choosen) {
            return true;
        } else {
            return false;
        }
    }
    choose = e => {
        let target = e.target;
        if (target.hasAttribute('data-pathkey')) {
            let pathKey = target.getAttribute('data-pathkey');
            let choosenPath;

            if (target.hasAttribute('data-choosearr')) {
                choosenPath = this.state.choosen;
                let tmp = choosenPath.split(' ');
                let idx = pathKey.split(' ').length;
                tmp[idx] = '[*]';
                choosenPath = tmp.join(' ');
            } else {
                choosenPath = pathKey;
            }

            this.setState(
                {
                    choosen: choosenPath,
                },
                () => {
                    let pathText = this.state.choosen;
                    pathText = pathText.replace(/ /g, '');
                    this.props.onChoose && this.props.onChoose(pathText);
                }
            );
        }
    };
    render() {
        let jsonObj;
        try {
            jsonObj = JSON.parse(this.props.json);
        } catch (error) {
            return <div>Wrong json string input</div>;
        }
        return (
            <div
                className="json-picker"
                onClick={this.props.showOnly ? null : this.choose}>
                {json2Jsx(this.state.choosen, jsonObj)}
            </div>
        );
    }
}
function isUrl(str) {
    let regexp = /^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    return regexp.test(str);
}

function escape(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function json2Jsx(choosenPath, jsonObj, isLast = true, pathKey = '') {
    if (jsonObj === null) {
        return renderNull(choosenPath, isLast, pathKey);
    } else if (jsonObj === undefined) {
        return renderUndefined(choosenPath, isLast, pathKey);
    } else if (Array.isArray(jsonObj)) {
        return renderArray(choosenPath, isLast, pathKey, jsonObj);
    } else if (typeof jsonObj == 'string') {
        return renderString(choosenPath, isLast, pathKey, jsonObj);
    } else if (typeof jsonObj == 'number') {
        return renderNumber(choosenPath, isLast, pathKey, jsonObj);
    } else if (typeof jsonObj == 'boolean') {
        return renderBoolean(choosenPath, isLast, pathKey, jsonObj);
    } else if (typeof jsonObj == 'object') {
        return renderObject(choosenPath, isLast, pathKey, jsonObj);
    } else {
        return null;
    }
}

// various types' render
function renderNull(choosenPath, isLast, pathKey) {
    return (
        <span className="json-literal">
            {pathKey.length > 0 ? (
                <span
                    role="img"
                    area-label="select path"
                    data-pathkey={pathKey}
                    className={getPickerStyle(
                        getRelationship(choosenPath, pathKey)
                    )}>
                    📋
                </span>
            ) : (
                ''
            )}

            <span>
                {'null'} {isLast ? '' : ','}
            </span>
        </span>
    );
}

function renderUndefined(choosenPath, isLast, pathKey) {
    return (
        <span className="json-literal">
            {pathKey.length > 0 ? (
                <i
                    data-pathkey={pathKey}
                    className={getPickerStyle(
                        getRelationship(choosenPath, pathKey)
                    )}>
                    📋
                </i>
            ) : (
                ''
            )}
            <span>
                {'undefined'} {isLast ? '' : ','}
            </span>
        </span>
    );
}

function renderString(choosenPath, isLast, pathKey, str) {
    str = escape(str);
    if (isUrl(str)) {
        return (
            <span>
                {pathKey.length > 0 ? (
                    <span
                        role="img"
                        area-label="sad"
                        data-pathkey={pathKey}
                        className={getPickerStyle(
                            getRelationship(choosenPath, pathKey)
                        )}>
                        📋
                    </span>
                ) : (
                    ''
                )}
                <a target="_blank" href={str} className="json-literal">
                    <span>
                        "{str}" {isLast ? '' : ','}
                    </span>
                </a>
            </span>
        );
    } else {
        return (
            <span className="json-literal">
                {pathKey.length > 0 ? (
                    <i
                        data-pathkey={pathKey}
                        className={getPickerStyle(
                            getRelationship(choosenPath, pathKey)
                        )}>
                        📋
                    </i>
                ) : (
                    ''
                )}
                <span>
                    "{str}" {isLast ? '' : ','}
                </span>
            </span>
        );
    }
}

function renderNumber(choosenPath, isLast, pathKey, num) {
    return (
        <span className="json-literal">
            {pathKey.length > 0 ? (
                <i
                    data-pathkey={pathKey}
                    className={getPickerStyle(
                        getRelationship(choosenPath, pathKey)
                    )}>
                    📋
                </i>
            ) : (
                ''
            )}
            <span>
                {num} {isLast ? '' : ','}
            </span>
        </span>
    );
}

function renderBoolean(choosenPath, isLast, pathKey, bool) {
    return (
        <span className="json-literal">
            {pathKey.length > 0 ? (
                <i
                    data-pathkey={pathKey}
                    className={getPickerStyle(
                        getRelationship(choosenPath, pathKey)
                    )}>
                    📋
                </i>
            ) : (
                ''
            )}
            <span>
                {bool} {isLast ? '' : ','}
            </span>
        </span>
    );
}

function renderObject(choosenPath, isLast, pathKey, obj) {
    let relation = getRelationship(choosenPath, pathKey);

    let keys = Object.keys(obj);
    let length = keys.length;
    if (length > 0) {
        return (
            <div className={relation === 1 ? 'json-picked_tree' : ''}>
                <div>
                    <span>{'{'}</span>
                    {pathKey.length > 0 ? (
                        <i
                            data-pathkey={pathKey}
                            className={getPickerStyle(relation)}>
                            📋
                        </i>
                    ) : (
                        ''
                    )}
                </div>
                <ul className="json-dict">
                    {keys.map((key, idx) => {
                        let nextPathKey = `${pathKey} .${key}`;
                        return (
                            <li key={nextPathKey}>
                                <span className="json-literal json-key">
                                    {key}
                                </span>
                                <span> : </span>
                                {json2Jsx(
                                    choosenPath,
                                    obj[key],
                                    idx === length - 1 ? true : false,
                                    nextPathKey
                                )}
                            </li>
                        );
                    })}
                </ul>
                <div>
                    {'}'} {isLast ? '' : ','}
                </div>
            </div>
        );
    } else {
        return (
            <span>
                <i data-pathkey={pathKey} className={getPickerStyle(relation)}>
                    📋
                </i>
                <span>
                    {'{ }'} {isLast ? '' : ','}
                </span>
            </span>
        );
    }
}

function renderArray(choosenPath, isLast, pathKey, arr) {
    let relation = getRelationship(choosenPath, pathKey);

    let length = arr.length;
    if (length > 0) {
        return (
            <div className={relation === 1 ? 'json-picked_tree' : ''}>
                <div>
                    {relation === 2 ? (
                        <i
                            data-pathkey={pathKey}
                            data-choosearr="1"
                            className={getPickArrStyle(choosenPath, pathKey)}>
                            [✚]
                        </i>
                    ) : null}
                    <span>{'['}</span>
                    {pathKey.length > 0 ? (
                        <i
                            data-pathkey={pathKey}
                            className={getPickerStyle(relation)}>
                            📋
                        </i>
                    ) : (
                        ''
                    )}
                </div>
                <ol className="json-array">
                    {arr.map((value, idx) => {
                        let nextPathKey = `${pathKey} [${idx}]`;
                        return (
                            <li key={nextPathKey}>
                                {json2Jsx(
                                    choosenPath,
                                    value,
                                    idx === length - 1 ? true : false,
                                    nextPathKey
                                )}
                            </li>
                        );
                    })}
                </ol>
                <div>
                    {']'} {isLast ? '' : ','}
                </div>
            </div>
        );
    } else {
        return (
            <span>
                {pathKey.length > 0 ? (
                    <i
                        data-pathkey={pathKey}
                        className={getPickerStyle(relation)}>
                        📋
                    </i>
                ) : (
                    ''
                )}
                <span>
                    {'[ ]'} {isLast ? '' : ','}
                </span>
            </span>
        );
    }
}

/**
 * get the relationship between now path and the choosenPath
 * 0 other
 * 1 self
 * 2 ancestor
 */
function getRelationship(choosenPath, path) {
    if (choosenPath === null) return 0;

    let choosenAttrs = choosenPath.split(' ');
    choosenAttrs.shift();
    let choosenLen = choosenAttrs.length;

    let nowAttrs = path.split(' ');
    nowAttrs.shift();
    let nowLen = nowAttrs.length;

    if (nowLen > choosenLen) return 0;

    for (let i = 0; i < nowLen; i++) {
        let ok;

        if (nowAttrs[i] === choosenAttrs[i]) {
            ok = true;
        } else if (
            nowAttrs[i][0] === '[' &&
            choosenAttrs[i][0] === '[' &&
            choosenAttrs[i][1] === '*'
        ) {
            ok = true;
        } else {
            ok = false;
        }

        if (!ok) return 0;
    }

    return nowLen === choosenLen ? 1 : 2;
}

/**
 * get picker's className, for ditinguishing picked or not or ancestor of picked entity
 */
function getPickerStyle(relation) {
    if (relation === 0) {
        return 'json-pick_path';
    } else if (relation === 1) {
        return 'json-pick_path json-picked';
    } else {
        return 'json-pick_path json-pick_path_ancestor';
    }
}

function getPickArrStyle(choosenPath, nowPath) {
    let csp = choosenPath.split(' ');
    let np = nowPath.split(' ');
    if (csp[np.length] === '[*]') {
        return 'json-pick_arr json-picked_arr';
    } else {
        return 'json-pick_arr';
    }
}
