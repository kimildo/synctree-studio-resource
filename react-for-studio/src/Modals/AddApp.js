import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
/* global global_data */

@inject('modalStore', 'appsStore')
@observer
class AddApp extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.state = {
            app_name: '',
            app_type: '',
            app_desc: '',
        };
    }

    makeApp(e) {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.appsStore.addApp(this.state);
        }
    }
    onChangeName(e) {
        this.setState({
            app_name: e.target.value,
        });
    }
    onChangeType(e) {
        this.setState({
            app_type: e.target.value,
        });
    }
    onChangeDesc(e) {
        this.setState({
            app_desc: e.target.value,
        });
    }

    closeModal() {
        this.props.modalStore.hideModal();
    }

    render() {
        const { dictionary } = global_data;

        return (
            <ModalWrapper>
                <form
                    className="modal-content"
                    ref={this.formEl}
                    onSubmit={this.makeApp.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span>Add app</span>
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body no-padding">
                            <div className="smart-form">
                                <fieldset>
                                    <section>
                                        <label className="label">
                                            app Name
                                        </label>
                                        <label className="input">
                                            <input
                                                type="text"
                                                onChange={this.onChangeName.bind(
                                                    this
                                                )}
                                                placeholder="Input App name"
                                                required="required"
                                            />
                                        </label>
                                    </section>
                                    <section>
                                        <label className="label">Type</label>
                                        <label className="select">
                                            <select
                                                required="required"
                                                onChange={this.onChangeType.bind(
                                                    this
                                                )}>
                                                <option value="">
                                                    --- select type ---
                                                </option>
                                                <option value="1">BIZ</option>
                                                <option value="2">
                                                    chatbot
                                                </option>
                                                <option value="3">page</option>
                                                <option value="4">
                                                    iframe
                                                </option>
                                            </select>
                                        </label>
                                    </section>
                                    <section>
                                        <label className="label">
                                            Description
                                        </label>
                                        <label className="input">
                                            <input
                                                type="text"
                                                onChange={this.onChangeDesc.bind(
                                                    this
                                                )}
                                                placeholder="Input Description"
                                            />
                                        </label>
                                    </section>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <ModalFooter>
                        <button type="submit" className="btn btn-primary">
                            <i className="fa fa-plus" /> {dictionary.button.add}
                        </button>
                    </ModalFooter>
                </form>
            </ModalWrapper>
        );
    }
}
export default AddApp;
