import React from 'react';
import OperatorLayer from './OperatorLayer';

const OperatorLayers = ({ ops }) => {
    return ops.map(op => (
        <OperatorLayer key={`operator_${op.op_id}`} op={op} />
    ));
};

export default OperatorLayers;
