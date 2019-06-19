import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Util from '../../../../../library/utils/Util';
/* global global_data */
@inject('modalStore', 'opsStore')
@observer
class BtnNormal extends Component {
    openEditOpModal(d) {
        this.props.modalStore.showModal({
            type: 'EditOperator',
            data: {
                type: 'edit',
                data: d,
            },
        });
    }
    openMappingOpModal(idxs, bindingSeq) {
        this.props.modalStore.showModal({
            type: 'Mapping',
            data: {
                opIdxs: idxs,
                bindingSeq: bindingSeq,
            },
        });
    }
    testOp() {
        this.props.modalStore.showModal({
            type: 'TestOp',
            data: {
                op_id: this.props.opIdx,
            },
        });
    }
    unbindOp() {
        const { opIdx } = this.props,
            { dictionary } = global_data;
        Util.confirmMessage(
            dictionary.alert.warn + '!',
            dictionary.alert.delete_ask
        ).then(() => {
            this.props.opsStore.moveOperatorsByIndex('unbind', [opIdx]);
        });
    }
    getBtnClass() {
        const { opIdx } = this.props;
        const partner = global_data.partner || null;

        let clsName = 'btn btn-declare-op btn-success dropdown-toggle';
        if (partner) {
            clsName += partner.edit_op_id !== opIdx ? ' disabled' : '';
        }
        return clsName;
    }
    render() {
        const btnCls = this.getBtnClass();
        const { data, controllInfo, midxs, bindingSeq, index } = this.props;
        return (
            <>
                <button className={btnCls} data-toggle="dropdown">
                    <span className="data-op">{data.op_text}</span>{' '}
                    <span className="caret" />
                    {data.is_new ? (
                        <div className="new">
                            <i className="fa fa-star" />
                        </div>
                    ) : (
                        ''
                    )}
                </button>
                <ul className="dropdown-menu">
                    <li>
                        {!controllInfo ? (
                            <button
                                title="Edit Operator"
                                className="btn-no-action modify-operator "
                                onClick={this.openEditOpModal.bind(this, data)}>
                                <i className="glyphicon glyphicon-edit" /> Edit
                                Operator
                            </button>
                        ) : (
                            ''
                        )}

                        {!global_data.partner ? (
                            <>
                                <button
                                    title="Mapping"
                                    className="btn-no-action modify-operator"
                                    onClick={this.openMappingOpModal.bind(
                                        this,
                                        midxs,
                                        bindingSeq
                                    )}>
                                    <i className="glyphicon glyphicon-transfer" />{' '}
                                    Mapping
                                </button>
                                <button
                                    title="Test"
                                    className="btn-no-action modify-operator"
                                    onClick={this.testOp.bind(this)}>
                                    <i className="fa fa-lg fa-fw fa-terminal" />{' '}
                                    Test
                                </button>
                                {!controllInfo ? (
                                    <button
                                        title="Unbind"
                                        className="btn-no-action modify-operator"
                                        onClick={this.unbindOp.bind(
                                            this,
                                            midxs,
                                            index
                                        )}>
                                        <i className="glyphicon glyphicon-eject" />{' '}
                                        Unbind
                                    </button>
                                ) : (
                                    ''
                                )}
                            </>
                        ) : (
                            ''
                        )}
                    </li>
                </ul>
            </>
        );
    }
}

export default BtnNormal;
