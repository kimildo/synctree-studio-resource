import React, { Component } from 'react';

export default class Basic extends Component {
    constructor(props) {
        super(props);
        const { index } = this.props;
        this.index = index;
    }

    onChangeEnv = e => {
        this.props.changeVal('env', this.index, e.target.value);
    };
    onChangeName = e => {
        this.props.changeVal('username', this.index, e.target.value);
    };

    onChangePass = e => {
        this.props.changeVal('password', this.index, e.target.value);
    };
    addField = () => {
        this.props.addParam();
    };
    removeField = () => {
        this.props.removeParam(this.props.index);
    };

    render() {
        const { env, username, password, len } = this.props;
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
                                value={env || ''}
                            />
                        </label>
                    </div>
                    <div className="col col-lg-3">
                        {this.index === 0 ? (
                            <label className="label">UserName</label>
                        ) : (
                            ''
                        )}

                        <label className="input">
                            <input
                                type="text"
                                onChange={this.onChangeName.bind(this)}
                                placeholder="Input UserName"
                                required="required"
                                value={username || ''}
                            />
                        </label>
                    </div>
                    <div className="col col-lg-4">
                        {this.index === 0 ? (
                            <label className="label">Password</label>
                        ) : (
                            ''
                        )}

                        <label className="input">
                            <input
                                type="text"
                                onChange={this.onChangePass.bind(this)}
                                placeholder="Input Password"
                                required="required"
                                value={password || ''}
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
