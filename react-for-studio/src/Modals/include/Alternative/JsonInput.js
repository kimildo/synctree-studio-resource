import React, { Component } from 'react';
import JsonPathPicker from '../JsonPathPicker';
import { inject, observer } from 'mobx-react';

@inject('alterStore')
@observer
class JsonInput extends Component {
    constructor(props) {
        super(props);
        this.state = {
            openJson: true,
        };
    }

    onClickJson = param => {
        this.props.alterStore.changeAlterData('jsonPath', `$${param}`);
        this.setState({ openJson: false });
    };
    openJsonPicker = () => {
        this.setState({ openJson: true });
    };
    render() {
        const { json, jsonPath } = this.props,
            { openJson } = this.state;
        return (
            <>
                <div className="col col-6">
                    <label className="input">
                        <input
                            type="text"
                            readOnly
                            autoComplete="off"
                            onClick={this.openJsonPicker}
                            value={jsonPath || ''}
                            placeholder="Value"
                        />
                    </label>
                </div>
                {!!openJson ? (
                    <div className="col col-10 relative mapping">
                        <label className="label">Click JSON value</label>
                        <JsonPathPicker
                            json={JSON.stringify(json)}
                            onChoose={this.onClickJson.bind(this)}
                        />
                    </div>
                ) : (
                    ''
                )}
            </>
        );
    }
}
export default JsonInput;
