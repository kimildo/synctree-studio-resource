import React from 'react';

const AlterExplain = ({ controllInfo, controlOperator }) => {
    const { base, my } = controllInfo;

    return (
        <div className="explain">
            [{' '}
            <strong>
                {base.operation_name || 'Biz'}
                {'.'}
                {base.parameter_key_name}
            </strong>{' '}
            {base.sub_parameter_path || ''} {controlOperator[my.operator]}{' '}
            <strong>{my.value}</strong>]
        </div>
    );
};
export default AlterExplain;
