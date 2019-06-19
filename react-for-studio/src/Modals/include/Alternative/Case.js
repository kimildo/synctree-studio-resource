import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

import ControllOperator from './ControllOperator';

@inject('alterStore', 'opsStore')
@observer
class Case extends Component {
    onChangeValue = e => {
        this.props.alterStore.changeAlterCaseData(
            this.props.index,
            'value',
            e.target.value
        );
    };
    onChangeOps = e => {
        this.props.alterStore.changeAlterCaseData(
            this.props.index,
            'opId',
            e.target.value
        );
    };
    getOps = () => {
        const { deselected } = this.props.opsStore;
        const { addOpts } = this.props;
        const opts = addOpts ? _.union(addOpts, deselected) : deselected;
        console.log('opts', opts);
        return opts.map(row => (
            <option key={`case_ops_${row.op_id}`} value={row.op_id}>
                {row.op_name}
            </option>
        ));
    };
    addcase = () => {
        this.props.alterStore.addCase();
    };
    removeCase = () => {
        this.props.alterStore.removeCase(this.props.index);
    };
    render() {
        const { data, index, len } = this.props;
        const { controll, value, opId } = data;
        return (
            <section className="req-data-frm">
                <div className="row">
                    <div className="col col-3">
                        <ControllOperator controll={controll} index={index} />
                    </div>

                    <div className="col col-2">
                        <label className="input">
                            <input
                                type="text"
                                maxLength="100"
                                autoComplete="off"
                                placeholder="Value"
                                onChange={this.onChangeValue}
                                value={value}
                                required="required"
                            />
                        </label>
                    </div>
                    <div className="col col-1 text-center">
                        <strong>:</strong>
                    </div>
                    <div className="col col-3">
                        <label className="select">
                            <select
                                onChange={this.onChangeOps}
                                value={opId}
                                required="required">
                                <option value="">-- Choose --</option>
                                {this.getOps()}
                            </select>
                            <i />
                        </label>
                    </div>
                    <div className="col col-3">
                        {index === len ? (
                            <button
                                className="btn btn-default btn-circle btn-req-plus"
                                type="button"
                                onClick={this.addcase}>
                                <i className="glyphicon glyphicon-plus" />
                            </button>
                        ) : (
                            ''
                        )}
                        {index >= 0 && len > 0 ? (
                            <button
                                className="btn btn-default txt-color-red btn-circle btn-req-minus "
                                type="button"
                                onClick={this.removeCase}>
                                <i className="glyphicon glyphicon-minus" />
                            </button>
                        ) : (
                            ''
                        )}
                    </div>
                </div>
            </section>
        );
    }
}
export default Case;
