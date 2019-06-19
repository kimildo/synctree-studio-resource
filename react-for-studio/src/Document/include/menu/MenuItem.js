import React from 'react';
const MenuItem = ({ op }) => {
    let method = op.req_method === 'G' ? 'get' : 'post';
    return (
        <li>
            <span className={`F${method}`}>{method.toUpperCase()}</span>
            <a href={`#${op.op_id}`}>{op.op_name}</a>
        </li>
    );
};
export default MenuItem;
