import React from 'react';

const BtnDisabled = ({ data }) => {
    return (
        <button
            className="btn btn-declare-op btn-success dropdown-toggle disabled"
            data-toggle="dropdown">
            <span className="data-op">{data.op_text}</span>
        </button>
    );
};

export default BtnDisabled;
