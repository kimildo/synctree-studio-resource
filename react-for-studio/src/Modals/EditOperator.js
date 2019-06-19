import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';

import Util from '../library/utils/Util';

import Loading from './Loading';
import BasicInfo from './include/EditOperator/BasicInfo';
import ReqForm from './include/EditOperator/ReqForm';
import ResForm from './include/EditOperator/ResForm';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';

/* global global_data */

@inject('bizStore', 'opStore', 'opsStore')
@observer
class EditOperator extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
    }
    componentDidMount() {
        const { data, opStore } = this.props,
            { operator } = opStore;

        if (data.type !== 'edit') {
            opStore.createOperator();
        } else if (!!operator) {
            if (data.data.op_id !== operator.op_id) {
                opStore.getOperator(data.data.op_id);
            }
        } else {
            opStore.getOperator(data.data.op_id);
        }
    }

    closeModal = () => {
        this.props.opStore.unsetOperator();
    };

    saveOperator = e => {
        e.preventDefault();
        const { dictionary } = global_data;
        const d = this.props.data;
        const { app_id, biz_id } = this.props.bizStore.bizInfo;

        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            Util.confirmMessage(
                dictionary.alert.warn + '!',
                dictionary.alert.add_ask
            ).then(() => {
                this.props.opStore.saveOperator(app_id).then(() => {
                    if (d.type === 'edit') {
                        this.props.bizStore.getBizUnit(app_id, biz_id);
                        this.props.bizStore.setBuildButton(true);
                    }

                    this.props.opsStore.getOperators();
                });
            });
        }
    };

    render() {
        const data = this.props.opStore.operator,
            { dictionary } = global_data,
            disabled = global_data.partner ? true : false;

        return (
            <ModalWrapper>
                {!!data ? (
                    <form
                        className="modal-content"
                        ref={this.formEl}
                        onSubmit={this.saveOperator}>
                        <ModalHeader closeModal={this.closeModal}>
                            <i className="fa fa-lg fa-fw fa-terminal" />{' '}
                            <span>Edit Operator</span>
                        </ModalHeader>
                        <div className="modal-body">
                            <div className="widget-body no-padding">
                                <div className="smart-form">
                                    <BasicInfo disabled={disabled} />
                                    <ReqForm disabled={disabled} />
                                    <ResForm />
                                </div>
                            </div>
                        </div>
                        <ModalFooter closeModal={this.closeModal}>
                            <button type="submit" className="btn btn-success">
                                <i className="fa fa-save" />{' '}
                                {dictionary.button.save}
                            </button>
                        </ModalFooter>
                    </form>
                ) : (
                    <Loading />
                )}
            </ModalWrapper>
        );
    }
}
export default EditOperator;
