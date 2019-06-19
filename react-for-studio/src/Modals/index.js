import React, { Component } from 'react';
import Modal from 'react-modal';
import { inject, observer } from 'mobx-react';

import MyLoadable from '../MyLoadable';
import customStyle from './customStyle';

const AddApp = MyLoadable({
    loader: () => import(/* webpackChunkName: "aa-m" */ './AddApp'),
});

const AddBiz = MyLoadable({
    loader: () => import(/* webpackChunkName: "ab-m" */ './AddBiz'),
});
const Alternative = MyLoadable({
    loader: () => import(/* webpackChunkName: "al-m" */ './Alternative'),
});

const BunitSetName = MyLoadable({
    loader: () => import(/* webpackChunkName: "bsn-m" */ './BunitSetName'),
});
const BunitShare = MyLoadable({
    loader: () => import(/* webpackChunkName: "bs-m" */ './BunitShare'),
});

const BindOpAddAuth = MyLoadable({
    loader: () => import(/* webpackChunkName: "bo-m" */ './BindOpAddAuth'),
});

const EditRequest = MyLoadable({
    loader: () => import(/* webpackChunkName: "er-m" */ './EditRequest'),
});

const EditOperator = MyLoadable({
    loader: () => import(/* webpackChunkName: "eo-m" */ './EditOperator'),
});

const Operators = MyLoadable({
    loader: () => import(/* webpackChunkName: "o-m" */ './Operators'),
});

const Mapping = MyLoadable({
    loader: () => import(/* webpackChunkName: "m-m" */ './Mapping'),
});

const SampleSource = MyLoadable({
    loader: () => import(/* webpackChunkName: "ss-m" */ './SampleSource'),
});

const TestBiz = MyLoadable({
    loader: () => import(/* webpackChunkName: "tb-m" */ './TestBiz'),
});

const TestOp = MyLoadable({
    loader: () => import(/* webpackChunkName: "to-m" */ './TestOp'),
});

const TimelineSetName = MyLoadable({
    loader: () => import(/* webpackChunkName: "tl-m" */ './TimelineSetName'),
});

@inject('modalStore')
@observer
class Modals extends Component {
    afterOpenModal() {}

    closeModal() {
        this.props.modalStore.hideModal();
    }

    getModalContents() {
        const { type, data } = this.props.modalStore.params;
        switch (type) {
            case 'BunitSetName':
                return <BunitSetName data={data} />;
            case 'EditRequest':
                return <EditRequest data={data} />;
            case 'EditOperator':
                return <EditOperator data={data} />;
            case 'Operators':
                return <Operators data={data} />;
            case 'Mapping':
                return <Mapping data={data} />;
            case 'SampleSource':
                return <SampleSource data={data} />;
            case 'AddApp':
                return <AddApp data={data} />;
            case 'AddBiz':
                return <AddBiz data={data} />;
            case 'TestBiz':
                return <TestBiz data={data} />;
            case 'TimelineSetName':
                return <TimelineSetName data={data} />;
            case 'BunitShare':
                return <BunitShare data={data} />;
            case 'TestOp':
                return <TestOp data={data} />;
            case 'BindOpAddAuth':
                return <BindOpAddAuth data={data} />;
            case 'Alternative':
                return <Alternative data={data} />;

            default:
                return '';
        }
    }

    render() {
        const { isOpen } = this.props.modalStore;

        let modalContents = '';
        if (isOpen) {
            modalContents = this.getModalContents();
        }
        return (
            <Modal
                isOpen={isOpen}
                closeTimeoutMS={500}
                onAfterOpen={this.afterOpenModal}
                onRequestClose={this.closeModal.bind(this)}
                ariaHideApp={false}
                style={customStyle}
                contentLabel="Set name">
                {modalContents}
            </Modal>
        );
    }
}

export default Modals;
