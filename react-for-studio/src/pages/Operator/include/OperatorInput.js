import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';

import Loading from '../../../Loading';
import Util from '../../../library/utils/Util';
import BasicInfo from '../../../Modals/include/EditOperator/BasicInfo';
import ReqForm from '../../../Modals/include/EditOperator/ReqForm';
import ResForm from '../../../Modals/include/EditOperator/ResForm';

/* global global_data */

@withRouter
@inject('operatorStore', 'appsStore', 'userInfoStore', 'opStore', 'modalStore')
@observer
class OperatorInput extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.state = { app_id: '' };
    }

    onSubmit(e) {
        e.preventDefault();
        const { dictionary } = global_data;

        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            Util.confirmMessage(
                dictionary.alert.warn + '!',
                dictionary.alert.add_ask
            ).then(() => {
                this.props.opStore
                    .saveOperator(this.state.app_id)
                    .then(this.gotoList.bind(this));
            });
        }
    }

    testOp() {
        this.props.modalStore.showModal({
            type: 'TestOp',
            data: {
                op_id: this.props.opId,
            },
        });
    }

    onChangeApp(e) {
        this.setState({
            app_id: e.target.value,
        });
    }

    updateSelectedApp() {
        const myapp = this.props.userInfoStore.getSelectedApp;
        if (myapp) {
            this.setState({
                app_id: myapp.app_id,
            });
        }
    }

    componentDidMount() {
        this.updateSelectedApp();
        const { type, opId } = this.props;

        if (type === 'Modify' && typeof opId !== 'undefined') {
            this.props.opStore.getOperator(opId);

            // });
        } else {
            this.props.opStore.createOperator();
        }
    }
    componentWillUnmount() {
        this.props.opStore.unsetOperatorByPage();
    }

    gotoList() {
        this.props.operatorStore.loadOperators();
        this.props.history.push(`/console/op/list`);
    }

    removeOperator() {
        const { app_id } = this.state;
        const { dictionary } = global_data;
        Util.confirmMessage(
            `${dictionary.alert.warn}!`,
            dictionary.alert.ask
        ).then(() => {
            this.props.opStore
                .removeOperator(app_id, [this.props.opId])
                .then(this.gotoList.bind(this));
        });
    }
    componentDidUpdate() {
        // this.updateSelectedApp();
    }

    render() {
        const data = this.props.opStore.operator;
        const { type } = this.props;
        const disabled = false;

        return (
            <div className="row">
                <article className="col-sm-12 col-md-12 col-lg-12">
                    <div
                        className="jarviswidget jarviswidget-color-blueDark"
                        role="widget">
                        <header role="heading" className="ui-sortable-handle">
                            <div className="jarviswidget-ctrls" role="menu" />
                            <span className="widget-icon">
                                {' '}
                                <i className="fa fa-edit" />{' '}
                            </span>
                            <h2>
                                {type !== 'Modify' ? 'Add' : 'Edit'} Operator{' '}
                            </h2>
                            {type === 'Modify' ? (
                                <button
                                    className="btn btn-xs btn-default test-op"
                                    onClick={this.testOp.bind(this)}>
                                    Test
                                </button>
                            ) : (
                                ''
                            )}

                            <span className="jarviswidget-loader">
                                <i className="fa fa-refresh fa-spin" />
                            </span>
                        </header>
                        <div role="content">
                            {data !== null ? (
                                <div className="widget-body no-padding">
                                    <form
                                        ref={this.formEl}
                                        onSubmit={this.onSubmit.bind(this)}
                                        className="smart-form">
                                        <BasicInfo disabled={disabled} />
                                        <ReqForm disabled={disabled} />
                                        <ResForm />

                                        <footer>
                                            <button
                                                type="submit"
                                                className="btn btn-success">
                                                <i className="fa fa-save" />{' '}
                                                <strong>Save</strong>
                                            </button>
                                            {type === 'Modify' ? (
                                                <button
                                                    type="button"
                                                    className="btn btn-danger"
                                                    onClick={this.removeOperator.bind(
                                                        this
                                                    )}>
                                                    <i className="fa fa-save" />{' '}
                                                    <strong>Delete</strong>
                                                </button>
                                            ) : (
                                                ''
                                            )}
                                            <Link
                                                to={`/console/op/list`}
                                                className="btn btn-default">
                                                Back
                                            </Link>
                                        </footer>
                                    </form>
                                </div>
                            ) : (
                                <Loading />
                            )}
                        </div>
                    </div>
                </article>
            </div>
        );
    }
}

export default OperatorInput;
