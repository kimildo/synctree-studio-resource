import React, { Component } from 'react';
class ModalWrapper extends Component {
    render() {
        return (
            <div
                className="fade in"
                tabIndex="-1"
                role="dialog"
                aria-labelledby="myModalLabel"
                aria-hidden="true">
                <div className="modal-dialog">{this.props.children}</div>
            </div>
        );
    }
}

export default ModalWrapper;
