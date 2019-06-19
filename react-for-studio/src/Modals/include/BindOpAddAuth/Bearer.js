import React, { Component } from 'react';

export default class Bearer extends Component {
    constructor(props) {
        super(props);
        this.index = this.props.index;
    }

    onChangeEnv = e => {
        this.props.changeVal('env', this.index, e.target.value);
    };
    onChangeToken = e => {
        this.props.changeVal('token', this.index, e.target.value);
    };
    addField = () => {
        this.props.addParam();
    };
    removeField = () => {
        this.props.removeParam(this.props.index);
    };

    render() {
        const { env, token, len } = this.props;
        return (
            <section>
                <div className="row">
                    <div className="col col-lg-3">
                        {this.index === 0 ? (
                            <label className="label">Environment</label>
                        ) : (
                            ''
                        )}
                        <label className="input">
                            <input
                                type="text"
                                onChange={this.onChangeEnv.bind(this)}
                                placeholder="Input Environment"
                                required="required"
                                value={env}
                            />
                        </label>
                    </div>
                    <div className="col col-lg-7">
                        {this.index === 0 ? (
                            <label className="label">Token</label>
                        ) : (
                            ''
                        )}
                        <label className="input">
                            <input
                                type="text"
                                onChange={this.onChangeToken.bind(this)}
                                placeholder="Input Token"
                                required="required"
                                value={token}
                            />
                        </label>
                    </div>
                    <div className="col col-lg-2">
                        {this.index === 0 ? (
                            <label className="label"> </label>
                        ) : (
                            ''
                        )}
                        {this.index === len ? (
                            <button
                                type="button"
                                className="btn btn-default btn-circle btn-req-plus "
                                onClick={this.addField}>
                                <i className="glyphicon glyphicon-plus" />
                            </button>
                        ) : (
                            ''
                        )}
                        {this.index >= 0 && len > 0 ? (
                            <button
                                type="button"
                                className="btn btn-default txt-color-red btn-circle btn-req-minus "
                                onClick={this.removeField}>
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
