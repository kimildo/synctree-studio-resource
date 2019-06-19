import React, { Component } from 'react';

import Util from '../../library/utils/Util';
import Request from '../../library/utils/Request';
/* global global_data */
class DeployHistoryData extends Component {
    request = Request;
    reDeploy = () => {
        const { app_id, biz_id } = this.props.biz;
        const { deploymentId } = this.props.data;
        this.request
            .post('/console/deploy', {
                app_id: parseInt(app_id),
                biz_id: biz_id,
                deploy_id: deploymentId,
            })
            .then(res => {
                if (!!res) {
                    Util.showSmallBox('success_landing');
                    // this.props.bizOpsStore.loadBizOps();
                }
            });
    };
    render() {
        const { data } = this.props;
        return (
            <tr>
                <td>{data.deploymentId}</td>
                <td>{data.bizOpsVersion || '-'}</td>
                <td>{data.status}</td>
                <td>{data.createTime}</td>
                <td>{data.completeTime}</td>
                <td>
                    {data.status === 'Succeeded'}
                    <button onClick={this.reDeploy} className="btn btn-default">
                        Deploy this version
                    </button>
                    {data.deploymentId}
                </td>
            </tr>
        );
    }
}

export default DeployHistoryData;
