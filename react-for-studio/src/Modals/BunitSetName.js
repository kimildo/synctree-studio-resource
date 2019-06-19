import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
/* global global_data */

@inject('modalStore', 'bizStore')
@observer
class BunitSetName extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
    }

    setName(e) {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.bizStore.saveBizUnit();
            this.closeModal();
        }
    }
    onChangeName(e) {
        const { bizStore } = this.props;
        bizStore.setClientTitle(e.target.value);
        e.preventDefault();
    }

    closeModal() {
        this.props.modalStore.hideModal();
    }

    render() {
        const { dictionary } = global_data;
        const { clientTitle } = this.props.bizStore;

        return (
            <ModalWrapper>
                <form
                    className="modal-content"
                    ref={this.formEl}
                    onSubmit={this.setName.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span id="">Node Name</span>
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body no-padding">
                            <div className="smart-form">
                                <fieldset>
                                    <section>
                                        <label className="label">Name</label>
                                        <label className="input">
                                            <input
                                                type="text"
                                                onChange={this.onChangeName.bind(
                                                    this
                                                )}
                                                placeholder="Input client name"
                                                required="required"
                                                value={clientTitle}
                                            />
                                        </label>
                                    </section>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <ModalFooter>
                        <button type="submit" className="btn btn-success">
                            <i className="fa fa-save" />{' '}
                            {dictionary.button.save}
                        </button>
                    </ModalFooter>
                </form>
            </ModalWrapper>
        );
    }
}
export default BunitSetName;
