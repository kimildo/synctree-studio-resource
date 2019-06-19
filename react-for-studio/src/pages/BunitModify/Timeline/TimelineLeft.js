/* eslint-disable jsx-a11y/anchor-is-valid */
import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
/* global global_data */

@inject('bizStore', 'modalStore')
@observer
class TimelineLeft extends Component {
    setName(e) {
        e.preventDefault();
        this.props.modalStore.showModal({ type: 'BunitSetName', data: '' });
    }
    editRequest(e) {
        e.preventDefault();
        this.props.modalStore.showModal({ type: 'EditRequest', data: '' });
    }
    getBtnClass() {
        const { opIdx } = this.props;
        const partner = global_data.partner || null;

        let clsName = 'btn btn-default btn-top dropdown-toggle padding-top-10';
        if (partner) {
            clsName += partner.edit_op_id !== opIdx ? ' disabled' : '';
        }
        return clsName;
    }

    render() {
        const { bizInfo, clientTitle } = this.props.bizStore;
        const btnCls = this.getBtnClass();
        return (
            <div id="timeline_left">
                <div>
                    <ul className="timeline-wrapper">
                        <li
                            className="smart-timeline"
                            style={{ height: this.props.h }}>
                            <div className="smart-timeline-list">
                                <div className="smart-timeline-icon btn-group no-border no-padding">
                                    <a
                                        title="Set Name"
                                        className={btnCls}
                                        data-toggle="dropdown">
                                        <span className="node-title">
                                            {clientTitle}
                                        </span>
                                        <span className="caret" />
                                    </a>
                                    <ul className="dropdown-menu">
                                        <li>
                                            <a
                                                title="Set Name"
                                                className="btn-no-action btn_set_node_name"
                                                onClick={this.setName.bind(
                                                    this
                                                )}>
                                                <i className="glyphicon glyphicon-edit" />
                                                Set Name
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div className="smart-timeline-content">
                                    <div className="left-static-request">
                                        <div className="line" />
                                    </div>
                                    <div className="left-static-response">
                                        <div className="line" />
                                        <div className="arrow">◀</div>
                                    </div>
                                    <section>
                                        {!global_data.partner ? (
                                            <a
                                                className="btn btn-sm btn-default set-client"
                                                onClick={this.editRequest.bind(
                                                    this
                                                )}>
                                                {bizInfo.req_method === 'N' ||
                                                !bizInfo.req_method ? (
                                                    <>
                                                        <i className="fa fa-exclamation-triangle text-danger" />{' '}
                                                        Set Request
                                                    </>
                                                ) : (
                                                    <>
                                                        <i className="glyphicon glyphicon-edit" />{' '}
                                                        Edit Request
                                                    </>
                                                )}
                                            </a>
                                        ) : (
                                            ''
                                        )}

                                        <div className="flow-arrow hidden">
                                            <div className="timeline-static-request">
                                                <div className="line" />
                                            </div>
                                            <div className="timeline-static-response">
                                                <div className="line" />
                                                <div className="arrow">◀</div>
                                            </div>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        );
    }
}
export default TimelineLeft;
