import React from 'react';
import { dataType } from '../../../library/constant/CommonConst';

const Param = ({ param, type }) => (
    <tr>
        <td>{param[`${type}_key`]}</td>
        <td>{dataType[param[`${type}_var_type`]]}</td>
        <td>
            {param.required_flag !== undefined ? (
                param.required_flag === 1 ? (
                    <strong>Required</strong>
                ) : (
                    'Optional'
                )
            ) : param[`${type}_required_flag`] === 1 ? (
                <strong>Required</strong>
            ) : (
                'Optional'
            )}
        </td>
        <td>{!!param[`${type}_desc`] ? `${param[`${type}_desc`]}` : '-'}</td>
    </tr>
);

export default Param;
