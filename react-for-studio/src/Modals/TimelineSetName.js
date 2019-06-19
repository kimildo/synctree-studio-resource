import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Util from '../library/utils/Util';
/* global global_data */

@inject('modalStore', 'bizStore')
@observer
class TimelineSetName extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.lineId = this.props.data.line_id;
        this.state = {
            lineTitle: this.props.bizStore.bizInfo.lines[this.lineId]
                .line_title,
        };
    }

    setName(e) {
        e.preventDefault();
        const { lineTitle } = this.state;
        const { bizStore } = this.props;
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            bizStore.setTimelineName(this.lineId, lineTitle);
            //this.closeModal();
        }
    }
    onChangeName(e) {
        // const { bizStore } = this.props;
        // bizStore.setClientTitle(e.target.value);
        // e.preventDefault();
        this.setState({
            lineTitle: e.target.value,
        });
    }

    closeModal() {
        this.props.modalStore.hideModal();
    }

    render() {
        const { dictionary } = global_data;
        const { lineTitle } = this.state;

        return (
            <ModalWrapper>
                <form
                    className="modal-content"
                    ref={this.formEl}
                    onSubmit={this.setName.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-puzzle-piece" />{' '}
                        <span>Set Name</span>
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
                                                placeholder="Input name"
                                                required="required"
                                                value={lineTitle}
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
export default TimelineSetName;
