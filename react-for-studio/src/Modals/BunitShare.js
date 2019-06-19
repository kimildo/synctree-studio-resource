import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';

import { Step1, Step2, Step3, Step4, Step5, Step6 } from './include/BunitShare';
/* global global_data */

@inject('modalStore', 'shareStore')
@observer
class BunitShare extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();

        this.biz = this.props.data.biz;
    }

    getStepLayer() {
        const step = this.props.shareStore.getStep;
        switch (step) {
            case 1:
            default:
                return <Step1 biz={this.biz} />;
            case 2:
                return <Step2 />;
            case 3:
                return <Step3 />;
            case 4:
                return <Step4 />;
            case 5:
                return <Step5 />;
            case 6:
                return <Step6 />;
        }
    }

    closeModal() {
        this.props.shareStore.closeStore();
    }
    componentDidMount() {
        const { app_id, biz_id } = this.biz;
        this.props.shareStore.setBaseId(parseInt(app_id), biz_id);
    }

    render() {
        const stepLayer = this.getStepLayer();

        return (
            <ModalWrapper>
                <div className="modal-content">
                    <ModalHeader closeModal={this.closeModal.bind(this)}>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span>Export</span>
                    </ModalHeader>
                    <div className="modal-body">{stepLayer}</div>
                    <ModalFooter closeModal={this.closeModal.bind(this)} />
                </div>
            </ModalWrapper>
        );
    }
}
export default BunitShare;
