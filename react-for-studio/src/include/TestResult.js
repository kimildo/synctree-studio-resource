import React, { Component } from 'react';
import _ from 'lodash';
import { restCode } from '../library/constant/CommonConst';
import Util from '../library/utils/Util';

class TestResult extends Component {
    state = { result: {} };
    step = 1;
    resultLayer = React.createRef();
    interval = 700;
    componentDidMount() {
        const { code, type } = this.props;

        const write = setInterval(() => {
            const { result } = this.state;
            switch (this.step) {
                case 1:
                    result.step1 = {
                        status: code.server_status,
                        targetUrl: code.request_target_url,
                        method: code.request_method,
                    };
                    if (!!code.request || !!code.results) {
                        this.step += 1;
                    } else {
                        result.last = 'Finished.';
                        clearInterval(write);
                    }
                    this.setState({ result: result });
                    break;
                case 2:
                    result.step2 = {
                        request: !_.isNull(code.request) ? code.request : null,
                    };
                    this.setState({ result: result });
                    this.step += 1;
                    break;
                case 3:
                    // const _this = this;
                    // if (type === 'biz') {
                    //     result.step3 = { response: [] };
                    //     const resp = Object.values(
                    //         code.results.data.responses
                    //     )[0];
                    //     let promise = Promise.resolve();

                    //     _.forEach(resp, r => {
                    //         promise = promise.then(() => {
                    //             return new Promise(function(resolve) {
                    //                 setTimeout(() => {
                    //                     result.step3.response.push(r);
                    //                     _this.setState({ result: result });
                    //                     resolve();
                    //                 }, _this.interval);
                    //             });
                    //         });
                    //     });
                    //     promise.then(function() {
                    //         result.last = 'Finished.';
                    //         _this.setState({ result: result });
                    //     });
                    // } else {
                    //     result.step3 = { response: code.response };
                    //     result.last = 'Finished.';
                    //     this.setState({ result: result });
                    // }
                    result.step3 = {
                        response: !_.isNull(code.results)
                            ? Util.checkJson(code.results)
                                ? JSON.parse(code.results)
                                : code.results
                            : null,
                    };
                    result.last = 'Finished.';
                    this.setState({ result: result });

                    clearInterval(write);

                    break;
                default:
                    break;
            }
        }, this.interval);
    }
    componentDidUpdate() {
        let h = this.resultLayer.current.scrollHeight;
        this.resultLayer.current.scrollTop = h;
    }
    getParams = (params, type) => {
        let returnData = [];
        if (_.isNull(params)) {
            returnData = <p>null</p>;
        } else if (typeof params === 'object') {
            _.forEach(params, (value, key) => {
                let v = _.isNull(value)
                    ? 'null'
                    : typeof value === 'object'
                    ? JSON.stringify(value)
                    : value;
                returnData.push(
                    <p key={`${type}_${key}`}>
                        {key} : {v}
                    </p>
                );
            });
        } else {
            returnData = <p>{params}</p>;
        }

        return returnData;
    };

    render() {
        const { result } = this.state;

        return (
            <div className="widget-body  margin-top-10">
                <h2 className="m-l-10">Test result</h2>
                <div className="test-result" ref={this.resultLayer}>
                    {!!result.step1 ? (
                        <>
                            <p
                                className={
                                    result.step1.status !== '200 OK'
                                        ? 'error'
                                        : 'success'
                                }>
                                Status : {result.step1.status}
                            </p>
                            <p>Target Url :{result.step1.targetUrl}</p>
                            <p>Method : {result.step1.method}</p>
                        </>
                    ) : (
                        ''
                    )}
                    {!!result.step2 ? (
                        <>
                            <p>Result</p>
                            <div className="result">
                                <p>Request</p>
                                <div className="request">
                                    {this.getParams(
                                        result.step2.request,
                                        'req'
                                    )}
                                </div>
                                {!!result.step3 ? (
                                    <>
                                        <p>Response</p>
                                        {Array.isArray(
                                            result.step3.response
                                        ) ? (
                                            <>
                                                {result.step3.response.map(
                                                    (res, i) => (
                                                        <div
                                                            key={`op_${i}`}
                                                            className="operator">
                                                            <p>
                                                                Operator name :{' '}
                                                                {res.op_name}
                                                            </p>
                                                            <p>
                                                                Target Url :
                                                                {
                                                                    res.request_target_url
                                                                }
                                                            </p>
                                                            <p
                                                                className={
                                                                    res.server_status !==
                                                                    '200 OK'
                                                                        ? 'error'
                                                                        : 'success'
                                                                }>
                                                                Status :{' '}
                                                                {
                                                                    res.server_status
                                                                }
                                                            </p>
                                                            <p>
                                                                Method :{' '}
                                                                {
                                                                    restCode[
                                                                        res
                                                                            .request_method
                                                                    ]
                                                                }
                                                            </p>
                                                            <p>Request</p>
                                                            <div className="request">
                                                                {this.getParams(
                                                                    res.request,
                                                                    `op_req_${i}`
                                                                )}
                                                            </div>
                                                            <p>Response</p>
                                                            <div className="response">
                                                                {this.getParams(
                                                                    res.response,
                                                                    `op_res_${i}`
                                                                )}
                                                            </div>
                                                        </div>
                                                    )
                                                )}
                                            </>
                                        ) : (
                                            <div className="response">
                                                {this.getParams(
                                                    result.step3.response,
                                                    `op_res_0`
                                                )}
                                            </div>
                                        )}
                                    </>
                                ) : (
                                    ''
                                )}
                            </div>
                        </>
                    ) : (
                        ''
                    )}
                    {!!result.last ? (
                        <p className="finish">{result.last}</p>
                    ) : (
                        ''
                    )}

                    <span className="underbar">_</span>
                </div>
            </div>
        );
    }
}

export default TestResult;
