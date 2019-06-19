import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
@inject('alterStore')
@observer
class ControllOperator extends Component {
    onChange = e => {
        this.props.alterStore.changeAlterCaseData(
            this.props.index,
            'controll',
            e.target.value
        );
    };
    getControll = () => {
        const { index } = this.props;
        const returnData = [];
        _.forEach(this.props.alterStore.controlOperator, (value, key) => {
            returnData.push(
                <option key={`alt_controll_${index}_${key}`} value={key}>
                    {value}
                </option>
            );
        });
        return returnData;
    };
    render() {
        const { controll } = this.props;
        return (
            <label className="select">
                <select value={controll} onChange={this.onChange}>
                    <option value={''}>-- Choose --</option>
                    {this.getControll()}
                </select>
                <i />
            </label>
        );
    }
}
export default ControllOperator;
