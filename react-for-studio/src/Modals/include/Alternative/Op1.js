import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

@inject('alterStore')
@observer
class Op1 extends Component {
    onChangeOp1 = e => {
        let v = e.target.value;
        if (!!v) {
            this.props.alterStore.changeAlterData('opt1', v);
            this.props.alterStore.changeAlterData('opt2', '');
            this.props.alterStore.changeAlterData('json', null);
            this.props.alterStore.changeAlterData('jsonPath', '');
        }
    };
    render() {
        const optsData = _.keys(this.props.alterStore.getData);
        const { opt1 } = this.props;
        return (
            <label className="select">
                <select value={opt1} onChange={this.onChangeOp1}>
                    <option value={''}>-- Choose -- </option>
                    {optsData.map((opt, i) => (
                        <option key={`alt_opt1_${i}`} value={opt}>
                            {opt}
                        </option>
                    ))}
                </select>
                <i />
            </label>
        );
    }
}
export default Op1;
