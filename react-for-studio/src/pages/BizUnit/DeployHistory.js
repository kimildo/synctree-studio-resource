import React from 'react';
import DeployHistoryData from './DeployHistoryData';

const DeployHistory = ({ historyData, biz }) => (
    <div className="table-responsive">
        <table className="table table-bordered">
            <thead>
                <tr>
                    <th>Deploy-id</th>
                    <th>Build Ver.</th>
                    <th>Status</th>
                    <th>Create Time</th>
                    <th>Complete Time</th>
                    <th> - </th>
                </tr>
            </thead>
            <tbody className="deploy-data">
                {historyData.map(row => {
                    return (
                        <DeployHistoryData
                            key={`DeployHistory_${biz.app_id}_${biz.biz_id}`}
                            biz={biz}
                            data={row}
                        />
                    );
                })}
            </tbody>
        </table>
    </div>
);
export default DeployHistory;
