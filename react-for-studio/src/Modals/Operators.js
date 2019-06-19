import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import Loading from './Loading';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
/* global global_data */

@inject('bizStore', 'modalStore', 'opsStore')
@observer
class Operators extends Component {
    constructor(props) {
        super(props);
        this.selectNon = React.createRef();
        this.selectSel = React.createRef();
        this.selectedOpData = [];
        this.deselectedOpData = [];
        this.closeModal = this.closeModal.bind(this);
    }

    componentDidMount() {
        if (this.props.data.type === 'getData') {
            this.props.opsStore.getOperators();
        }
    }

    createOperator() {
        this.props.modalStore.showModal({
            type: 'EditOperator',
            data: {
                type: 'create',
            },
        });
    }

    getSelectOption(data) {
        const returnData = data.map((row, i) => {
            return (
                <option
                    value={row.op_id}
                    key={`getSelectOption_${row.op_id}_${i}`}>
                    {row.op_name}
                </option>
            );
        });
        return returnData;
    }
    move(type, options) {
        const idxs = [];
        if (options.length > 0) {
            _.forEach(options, opt => {
                idxs.push(opt.index);
            });
            this.props.opsStore.moveOperatorsByIndex(type, idxs);
        }
    }

    moveAll(type) {
        this.props.opsStore.moveOperatorsAll(type);
    }
    selectMulti() {
        const options = this.selectNon.current.selectedOptions;
        this.move('bind', options);
    }
    selectAll() {
        this.moveAll('bind');
    }
    deselectMulti() {
        const options = this.selectSel.current.selectedOptions;
        this.move('unbind', options);
    }
    deselectAll() {
        this.moveAll('unbind');
    }
    closeModal() {
        this.props.opsStore.unsetOperators();
    }

    getViewLayer() {
        const { selected, deselected, load } = this.props.opsStore;
        const { dictionary } = global_data;
        let returnData = <Loading />;

        if (load) {
            const selectedOption = this.getSelectOption(selected);
            const deselectedOption = this.getSelectOption(deselected);

            returnData = (
                <ModalWrapper>
                    <div className="modal-content">
                        <ModalHeader closeModal={this.closeModal}>
                            <i className="fa fa-lg fa-fw fa-terminal" /> Select
                            Operator
                        </ModalHeader>
                        <div className="modal-body">
                            <div className="widget-body no-padding">
                                <form className="smart-form">
                                    <fieldset>
                                        <section className="row sec-multiselect">
                                            <div className="col col-5">
                                                <label className="select select-multiple">
                                                    <select
                                                        className="form-control custom-scroll"
                                                        multiple="multiple"
                                                        ref={this.selectNon}
                                                        size="10">
                                                        {/* loop operators */}
                                                        {deselectedOption}
                                                    </select>
                                                </label>
                                            </div>
                                            <div className="col">
                                                <div className="trans-button-wrap">
                                                    <button
                                                        className="btn btn-default btn-circle"
                                                        type="button"
                                                        onClick={this.selectMulti.bind(
                                                            this
                                                        )}>
                                                        <i className="glyphicon glyphicon-arrow-right" />{' '}
                                                    </button>
                                                    <button
                                                        className="btn btn-default btn-circle "
                                                        type="button"
                                                        onClick={this.selectAll.bind(
                                                            this
                                                        )}>
                                                        <i className="glyphicon glyphicon-forward" />{' '}
                                                    </button>
                                                    <button
                                                        className="btn btn-default btn-circle "
                                                        type="button"
                                                        onClick={this.deselectMulti.bind(
                                                            this
                                                        )}>
                                                        <i className="glyphicon glyphicon-arrow-left" />{' '}
                                                    </button>
                                                    <button
                                                        className="btn btn-default btn-circle "
                                                        type="button"
                                                        onClick={this.deselectAll.bind(
                                                            this
                                                        )}>
                                                        <i className="glyphicon glyphicon-backward" />{' '}
                                                    </button>
                                                </div>
                                            </div>
                                            <div
                                                className="col col-5"
                                                style={{ float: 'right' }}>
                                                <label className="select select-multiple">
                                                    <select
                                                        className="form-control custom-scroll"
                                                        multiple="multiple"
                                                        size="10"
                                                        ref={this.selectSel}
                                                        required="required">
                                                        {selectedOption}
                                                    </select>
                                                </label>
                                            </div>
                                        </section>

                                        <section className="text-align-right margin-top20">
                                            <button
                                                type="button"
                                                className="btn btn-primary padding-5"
                                                onClick={this.createOperator.bind(
                                                    this
                                                )}>
                                                <i className="fa fa-plus" />{' '}
                                                {dictionary.button.add_op}
                                            </button>
                                        </section>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        <ModalFooter closeModal={this.closeModal}>
                            <button
                                type="button"
                                className="btn btn-primary"
                                onClick={this.closeModal.bind(this)}>
                                <i className="fa fa-plus" />{' '}
                                {dictionary.button.apply}
                            </button>
                        </ModalFooter>
                    </div>
                </ModalWrapper>
            );
        }
        return returnData;
    }
    render() {
        const view = this.getViewLayer();
        return <>{view}</>;
    }
}

export default Operators;
