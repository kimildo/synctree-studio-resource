import React from 'react';
import { Link, withRouter } from 'react-router-dom';

const OperatorLayer = ({ op }) => {
    return (
        <div className="col-md-3 col-md-offset-1">
            <Link to={`/console/op/modify/${op.op_id}`}>
                <h5>{op.op_name}</h5>
                <p>{op.op_desc}</p>
                <p>{op.regist_date}</p>
            </Link>
        </div>
    );
};

export default withRouter(OperatorLayer);
