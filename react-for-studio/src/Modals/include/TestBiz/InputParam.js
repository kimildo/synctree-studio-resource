import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
/* global global_data */

@observer
class InputParam extends Component {
    onChangeVal(e) {
        const { paramKey } = this.props;
        this.props.onChangeValue(paramKey, e.target.value);
    }

    render() {
        const { paramKey, value } = this.props;
        return (
            <div className="req-data-frm-field">
                <section className="req-data-frm">
                    <div className="row">
                        <div className="col col-2">
                            <label className="input key">{paramKey}</label>
                        </div>
                        <div className="col col-10">
                            <label className="input ">
                                <input
                                    type="text"
                                    onChange={this.onChangeVal.bind(this)}
                                    placeholder="Sample value"
                                    value={value}
                                    required=""
                                />
                            </label>
                        </div>
                    </div>
                </section>
            </div>
        );
    }
}

export default InputParam;
