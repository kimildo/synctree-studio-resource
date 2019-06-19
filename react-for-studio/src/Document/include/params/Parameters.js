import React from 'react';

import Param from './Param';
const getReqParams = (params, type) =>
    params.map(param => (
        <Param
            key={`getReqParams_${param[`${type}_key`]}`}
            param={param}
            type={type}
        />
    ));

const Parameters = ({ params, type, id }) => (
    <table className="paramTable" key={id}>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Tags</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>{getReqParams(params, type)}</tbody>
    </table>
);

export default Parameters;
