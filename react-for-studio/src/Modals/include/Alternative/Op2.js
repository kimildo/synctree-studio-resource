import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

@inject('alterStore')
@observer
class Op2 extends Component {
    onChangeOp = e => {
        const { opt1 } = this.props;
        let v = e.target.value;
        if (!!v) {
            const d = _.find(this.props.alterStore.getData[opt1], {
                param_id: parseInt(v),
            });
            this.props.alterStore.changeAlterData('opt2', parseInt(v));
            if (d.param_var_type === 'JSN' && !!d.sub_parameter_format) {
                this.props.alterStore.changeAlterData(
                    'json',
                    d.sub_parameter_format
                );
            } else {
                this.props.alterStore.changeAlterData('json', null);
            }
            this.props.alterStore.changeAlterData('jsonPath', '');
        }
    };
    render() {
        const { opt1, opt2 } = this.props;
        const optsData = this.props.alterStore.getData[opt1];

        return (
            <label className="select">
                <select value={opt2} onChange={this.onChangeOp}>
                    <option value={''}>-- Choose -- </option>
                    {optsData.map(opt => (
                        <option
                            key={`alt_opt2_${opt.param_id}`}
                            value={opt.param_id}>
                            {opt.param_key}
                        </option>
                    ))}
                </select>
                <i />
            </label>
        );
    }
}
export default Op2;
