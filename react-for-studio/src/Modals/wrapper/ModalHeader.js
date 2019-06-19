import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
@inject('modalStore')
@observer
class ModalHeader extends Component {
    closeModal() {
        if (this.props.closeModal) {
            this.props.closeModal();
            return;
        }
        this.props.modalStore.hideModal();
    }
    render() {
        return (
            <div className="modal-header">
                <button
                    type="button"
                    className="close"
                    onClick={this.closeModal.bind(this)}
                    aria-hidden="true">
                    ×
                </button>
                <h4 className="modal-title">{this.props.children}</h4>
            </div>
        );
    }
}

export default ModalHeader;
