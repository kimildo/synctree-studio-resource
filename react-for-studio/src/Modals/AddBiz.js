import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
/* global global_data */

@inject('modalStore', 'bizOpsStore')
@observer
class AddBiz extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        const { appId } = this.props.data;
        this.state = {
            app_id: appId,
            biz_name: '',
            biz_desc: '',
        };
    }

    makeBizUnit(e) {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.bizOpsStore.addBizOps(this.state);
        }
    }
    onChangeName(e) {
        this.setState({
            biz_name: e.target.value,
        });
    }
    onChangeDesc(e) {
        this.setState({
            biz_desc: e.target.value,
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
                    onSubmit={this.makeBizUnit.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span>Add Biz Unit</span>
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body no-padding">
                            <div className="smart-form">
                                <fieldset>
                                    <section>
                                        <label className="label">
                                            Biz Unit Name
                                        </label>
                                        <label className="input">
                                            <input
                                                type="text"
                                                onChange={this.onChangeName.bind(
                                                    this
                                                )}
                                                placeholder="Input Bizunit name"
                                                required="required"
                                            />
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
export default AddBiz;
