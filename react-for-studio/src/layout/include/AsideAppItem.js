import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link } from 'react-router-dom';
import _ from 'lodash';

@inject('bizOpsStore')
@observer
class AsideAppItem extends Component {
    componentDidMount() {
        const { app, bizOpsStore } = this.props;
        if (!bizOpsStore && !!app) {
            bizOpsStore.loadBizOps(app.app_id);
        }
    }

    getClsName(appId, bizId) {
        return _.includes(
            this.props.path,
            `/console/apps/bunit/${appId}/modify/${bizId}`
        )
            ? 'active'
            : '';
    }
    getBizOps = appId => {
        const ops = this.props.bizOpsStore.getBizOps;

        if (!ops) {
            return '';
        }
        if (ops.length === 0) {
            return '';
        }
        if (parseInt(ops[0].app_id) !== appId) {
            return '';
        }

        const submenu = ops.map(op => (
            <li
                key={`menu_biz_${op.biz_id}`}
                className={this.getClsName(appId, op.biz_id)}>
                <Link
                    to={`/console/apps/bunit/${appId}/modify/${
                        op.biz_id
                    }/unitprop`}>
                    {op.biz_name}
                </Link>
            </li>
        ));
        return <ul className={'submenu'}>{submenu}</ul>;
    };
    render() {
        const { app, clsName, bizOpsStore } = this.props,
            ops = bizOpsStore.getBizOps;
        return (
            <li className={clsName}>
                <Link to={`/console/apps/bunit/${app.app_id}`}>
                    {app.app_name}
                </Link>
                {!!ops ? this.getBizOps(app.app_id) : ''}
            </li>
        );
    }
}

export default AsideAppItem;
