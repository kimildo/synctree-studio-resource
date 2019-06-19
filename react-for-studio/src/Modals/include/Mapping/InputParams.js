import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import JsonPathPicker from '../JsonPathPicker';

@inject('mappingStore')
@observer
class InputParams extends Component {
    constructor(props) {
        super(props);
        this.index = this.props.index;

        const { data } = this.props;

        this.state = {
            jsonMapping: !!data.relay_sub_parameter_path || false,
            jsonStr: data.relay_sub_parameter_format || '',
            jsonOpen: false,
        };
    }

    getRelayVars() {
        const { mappingGetData } = this.props.mappingStore;
        const { data } = this.props;
        const returnData = [];

        console.log('mappingGetData', data);

        _.forOwn(mappingGetData, (value, key) => {
            let returnInnerData = value.map((row, i) => {
                // if (row.param_var_type === data.param_var_type) {
                //row.param_var_type === data.param_var_type
                let j = JSON.stringify({
                    id: row.param_id,
                    key: key,
                    seq: key === 'biz' ? null : row.binding_seq,
                });
                return (
                    <option
                        value={j}
                        key={`InputParams_getRelayVars_${
                            this.index
                        }_${key}_${i}`}>
                        {key} : {row.param_key}
                    </option>
                );
                // }
            });
            // if (returnInnerData.length > 0) {
            returnData.push(
                <optgroup
                    label={key}
                    key={`InputParams_getRelayVars__${this.index}_${key}`}>
                    {returnInnerData}
                </optgroup>
            );
            // }
        });
        const keyStr = !!_.find(mappingGetData['biz'], {
            param_id: data.relay_parameter_id,
        })
            ? 'biz'
            : 'ops';

        return (
            <>
                <select
                    required="required"
                    value={
                        JSON.stringify({
                            id: data.relay_parameter_id,
                            key: keyStr,
                            seq: data.relay_binding_seq,
                        }) || ''
                    }
                    onChange={this.onChangeRelayParamId.bind(this)}>
                    <option value="">-- Choose --</option>
                    {returnData}
                </select>
                <i />
            </>
        );
    }
    onChangeRelayFlag(e) {
        let v = parseInt(e.target.value);
        if (v === 0) {
            this.setState({
                jsonMapping: false,
                jsonStr: '',
            });
        }
        this.props.mappingStore.changeMappingData(
            this.index,
            'relay_flag',
            parseInt(e.target.value)
        );
    }
    onChangeArgValue(e) {
        this.props.mappingStore.changeMappingData(
            this.index,
            'argument_value',
            e.target.value
        );
    }
    onChangeRelayParamId(e) {
        const { mappingGetData } = this.props.mappingStore;
        let params = JSON.parse(e.target.value);

        const data = _.find(mappingGetData[params.key], {
            param_id: params.id,
        });

        // {`${row.param_id}:${key}:${row.param_key}`

        if (data) {
            this.props.mappingStore.changeMappingData(
                this.index,
                'relay_parameter_id',
                params.id
            );
            this.props.mappingStore.changeMappingData(
                this.index,
                'relay_binding_seq',
                params.seq
            );
            if (data.param_var_type === 'JSN') {
                this.setState({
                    jsonMapping: true,
                    jsonStr: data.sub_parameter_format,
                    jsonOpen: true,
                });
            } else {
                this.setState({
                    jsonMapping: false,
                    jsonStr: '',
                    jsonOpen: false,
                });
                this.props.mappingStore.changeMappingData(
                    this.index,
                    'relay_sub_parameter_path',
                    ''
                );
            }
        }
    }
    openJsonPicker = () => {
        this.setState({ jsonOpen: true });
    };

    onClickJson = param => {
        this.props.mappingStore.changeMappingData(
            this.index,
            'relay_sub_parameter_path',
            `$${param}`
        );
        this.setState({ jsonOpen: false });
    };

    render() {
        const { jsonMapping, jsonStr, jsonOpen } = this.state,
            { data } = this.props;

        return (
            <section className="req-data-frm">
                <div className="row">
                    <div className="col col-2">
                        {this.index === 0 ? (
                            <label className="label">
                                <span className="text-danger">*</span> Key
                            </label>
                        ) : (
                            ''
                        )}
                        <label className="input">
                            <input type="text" value={data.key} readOnly />
                        </label>
                    </div>
                    <div className="col col-2">
                        {this.index === 0 ? (
                            <label className="label">
                                <span className="text-danger">*</span> Value
                                Type
                            </label>
                        ) : (
                            ''
                        )}
                        <label className="select">
                            <select
                                required="required"
                                value={data.relay_flag}
                                onChange={this.onChangeRelayFlag.bind(this)}>
                                <option value="0">Constant</option>
                                <option value="1">Relay</option>
                            </select>

                            <i />
                        </label>
                    </div>

                    <div
                        className={
                            !!jsonMapping ? 'col col-3' : 'col col-6 p-r-0'
                        }>
                        {this.index === 0 ? (
                            <label className="label">
                                <span className="text-danger">*</span> Value
                            </label>
                        ) : (
                            ''
                        )}
                        {parseInt(data.relay_flag) === 0 ? (
                            <label className="input">
                                <input
                                    type="text"
                                    maxLength="100"
                                    autoComplete="off"
                                    onChange={this.onChangeArgValue.bind(this)}
                                    value={data.argument_value || ''}
                                    placeholder="Value"
                                />
                            </label>
                        ) : (
                            <label className="select">
                                {this.getRelayVars()}
                            </label>
                        )}
                    </div>

                    {!!jsonMapping ? (
                        <div className={'col col-3 p-r-0'}>
                            {this.index === 0 ? (
                                <label className="label">
                                    <span className="text-danger">*</span> JSON
                                    path
                                </label>
                            ) : (
                                ''
                            )}
                            <label className="input">
                                <input
                                    type="text"
                                    readOnly
                                    onClick={this.openJsonPicker}
                                    autoComplete="off"
                                    value={data.relay_sub_parameter_path || ''}
                                    placeholder="JSON Path"
                                />
                            </label>
                        </div>
                    ) : (
                        ''
                    )}
                    {!!jsonMapping && !!jsonStr && !!jsonOpen ? (
                        <div className="row">
                            <div className="col col-10 relative mapping">
                                <label className="label">
                                    Click JSON value
                                </label>
                                <JsonPathPicker
                                    json={JSON.stringify(jsonStr)}
                                    onChoose={this.onClickJson.bind(this)}
                                />
                            </div>
                        </div>
                    ) : (
                        ''
                    )}
                </div>
            </section>
        );
    }
}

export default InputParams;
