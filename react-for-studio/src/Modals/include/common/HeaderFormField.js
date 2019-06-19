import React, { Component } from 'react';

class HeaderFormField extends Component {
    constructor(props) {
        super(props);
        this.index = this.props.index;
    }
    onChangeKey = e => {
        this.props.onChangeKey(this.index, e.target.value);
    };
    onChangevalue = e => {
        this.props.onChangevalue(this.index, e.target.value);
    };
    addFormField = () => {
        this.props.addFormField();
    };
    removeFormField = () => {
        this.props.removeFormField(this.index);
    };
    render() {
        const { data, len } = this.props;
        return (
            <section className="req-data-frm">
                <div className="row">
                    <div className="col col-2">
                        <label className="input">
                            <input
                                type="text"
                                maxLength="100"
                                autoComplete="off"
                                placeholder="Key"
                                className="required"
                                required={true}
                                value={data.key}
                                onChange={this.onChangeKey}
                            />
                        </label>
                    </div>
                    <div className="col col-8 p-r-0">
                        <label className="input">
                            <input
                                type="text"
                                className="input-key"
                                maxLength="100"
                                autoComplete="off"
                                placeholder="value"
                                required={true}
                                value={data.value}
                                onChange={this.onChangevalue}
                            />
                        </label>
                    </div>

                    <div className="col col-2 p-r-0">
                        {this.index === len ? (
                            <button
                                className="btn btn-default btn-circle btn-req-plus"
                                type="button"
                                onClick={this.addFormField}>
                                <i className="glyphicon glyphicon-plus" />
                            </button>
                        ) : (
                            ''
                        )}
                        {this.index >= 0 && len > 0 ? (
                            <button
                                className="btn btn-default txt-color-red btn-circle btn-req-minus "
                                type="button"
                                onClick={this.removeFormField}>
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

export default HeaderFormField;
