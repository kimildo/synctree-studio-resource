import React from 'react';

const RequestMethod = ({ reqMethod, onChangeMethod }) => {
    return (
        <section>
            <label className="label">
                <span className="text-danger">*</span> Request Method
            </label>
            <div className="inline-group">
                <label className="radio">
                    <input
                        type="radio"
                        name="req_method"
                        value="G"
                        onChange={onChangeMethod}
                        checked={
                            reqMethod === 'G' || reqMethod === null
                                ? 'checked'
                                : ''
                        }
                    />
                    <i />
                    GET(query)
                </label>
                <label className="radio">
                    <input
                        type="radio"
                        name="req_method"
                        value="P"
                        onChange={onChangeMethod}
                        checked={reqMethod === 'P' ? 'checked' : ''}
                    />
                    <i />
                    POST
                </label>
                <label className="radio">
                    <input
                        type="radio"
                        name="req_method"
                        value="C"
                        onChange={onChangeMethod}
                        checked={reqMethod === 'C' ? 'checked' : ''}
                    />
                    <i />
                    GET(Clean URL)
                </label>
                <label className="radio">
                    <input
                        type="radio"
                        name="req_method"
                        value="U"
                        onChange={onChangeMethod}
                        checked={reqMethod === 'U' ? 'checked' : ''}
                    />
                    <i />
                    PUT
                </label>
                <label className="radio">
                    <input
                        type="radio"
                        name="req_method"
                        value="D"
                        onChange={onChangeMethod}
                        checked={reqMethod === 'D' ? 'checked' : ''}
                    />
                    <i />
                    DELETE
                </label>
            </div>
        </section>
    );
};

export default RequestMethod;
