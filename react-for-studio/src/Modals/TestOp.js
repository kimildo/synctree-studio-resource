import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

import Util from '../library/utils/Util';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Request from '../library/utils/Request';
import InputParam from './include/TestBiz/InputParam';
import Loading from '../Loading';
import TestResult from '../include/TestResult';

@inject('modalStore', 'userInfoStore', 'opStore')
@observer
class TestOp extends Component {
    request = Request;
    formEl = React.createRef();
    state = {
        params: [],
        objParams: {},
        result: null,
        loading: false,
        storeChanged: false,
    };

    componentDidMount() {
        const d = this.props.data,
            { operator } = this.props.opStore;
        if (!operator || d.op_id !== operator.op_id) {
            this.props.opStore.getOperator(d.op_id);
        } else {
            this.setData();
        }
    }
    componentWillReact() {
        if (!this.state.storeChanged) {
            this.setData();
        }
    }

    setData() {
        const { operator } = this.props.opStore;
        let p = {};
        _.forEach(operator.request, d => {
            p[d.req_key] = '';
        });
        this.setState({
            params: operator.request,
            objParams: p,
            storeChanged: true,
        });
    }

    onChangeValue(key, value) {
        this.setState(prevState => {
            prevState.objParams[key] = value;
            return prevState;
        });
    }

    closeModal() {
        this.props.modalStore.hideModal();
    }
    test(e) {
        e.preventDefault();
        const { op_id } = this.props.data,
            { loading } = this.state;
        if (loading) {
            return false;
        }
        let submitData = {
            op_id: op_id,
        };
        _.forOwn(this.state.objParams, (value, key) => {
            submitData[key] = value;
        });
        this.setState({
            loading: true,
        });
        this.request
            .post('/console/apps/op/test', submitData)
            .then(res => res.data.data)
            .then(data => {
                Util.showSmallBox('success_landing');
                this.setState({
                    result: data || {},
                    loading: false,
                });
            })
            .catch(data => {
                Util.showSmallBox('error');
                this.setState({
                    loading: false,
                });
            });
    }

    getInputLayer() {
        const { objParams } = this.state;
        const returnData = [];
        _.forOwn(objParams, (value, key) => {
            returnData.push(
                <InputParam
                    value={value}
                    key={`InputParam_${key}`}
                    paramKey={key}
                    onChangeValue={this.onChangeValue.bind(this)}
                />
            );
        });
        return returnData;
    }

    render() {
        const { loading, result, storeChanged } = this.state;
        const { operator } = this.props.opStore;
        const testInputLayer = this.getInputLayer.bind(this).call();
        return (
            <ModalWrapper>
                <div className="modal-content">
                    {!storeChanged ? (
                        <Loading />
                    ) : (
                        <>
                            <ModalHeader>
                                <i className="fa fa-lg fa-fw fa-terminal" />{' '}
                                <span>Test Operator</span>
                            </ModalHeader>
                            <div className="modal-body">
                                <div className="widget-body no-padding">
                                    <form
                                        className="smart-form"
                                        ref={this.formEl}
                                        onSubmit={this.test.bind(this)}>
                                        <fieldset>
                                            <section
                                                className="req-data-frm"
                                                style={{ marginBottom: '0px' }}>
                                                <div className="row">
                                                    <div className="col col-2">
                                                        <label className="label">
                                                            <span className="text-danger">
                                                                *
                                                            </span>
                                                            Key
                                                        </label>
                                                    </div>
                                                    <div className="col col-6">
                                                        <label className="label">
                                                            <span className="text-danger">
                                                                *
                                                            </span>
                                                            Value
                                                        </label>
                                                    </div>
                                                </div>
                                            </section>
                                            {testInputLayer}
                                        </fieldset>
                                        <p
                                            className="text-right"
                                            style={{ paddingRight: '14px' }}>
                                            <button
                                                type="submit"
                                                className="btn btn-primary"
                                                style={{
                                                    padding: '6px 12px',
                                                }}>
                                                {loading ? (
                                                    <i className="fa fa-circle-o-notch fa-spin" />
                                                ) : (
                                                    <>
                                                        <i className="fa fa-plus" />{' '}
                                                        Test Submit
                                                    </>
                                                )}
                                            </button>
                                        </p>
                                    </form>
                                    {!!result && !loading ? (
                                        <TestResult code={result} type={'op'} />
                                    ) : loading ? (
                                        <Loading />
                                    ) : (
                                        ''
                                    )}
                                </div>
                            </div>
                            <ModalFooter />
                        </>
                    )}
                </div>
            </ModalWrapper>
        );
    }
}
export default TestOp;
