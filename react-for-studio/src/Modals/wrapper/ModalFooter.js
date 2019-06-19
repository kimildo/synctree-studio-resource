import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
/* global global_data */
@inject('modalStore')
@observer
class ModalFooter extends Component {
    closeModal() {
        if (this.props.closeModal) {
            this.props.closeModal();
            return;
        }
        this.props.modalStore.hideModal();
    }
    render() {
        const { dictionary } = global_data;
        return (
            <div className="modal-footer">
                <button
                    type="button"
                    className="btn btn-default"
                    onClick={this.closeModal.bind(this)}>
                    <i className="fa fa-ban" /> {dictionary.button.close}
                </button>
                {this.props.children}
            </div>
        );
    }
}

export default ModalFooter;
