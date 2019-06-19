import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import AsideAppItem from './AsideAppItem';
import _ from 'lodash';
@inject('appsStore')
@observer
class AsideApps extends Component {
    getAppClsName(app_id) {
        return _.includes(this.props.path, `/console/apps/bunit/${app_id}`)
            ? 'active'
            : '';
    }
    render() {
        const apps = this.props.appsStore.getApps;
        if (!apps) {
            return '';
        }
        return (
            <ul>
                {apps.map(app => (
                    <AsideAppItem
                        app={app}
                        key={`aside_app_item_${app.app_id}`}
                        path={this.props.path}
                        clsName={this.getAppClsName
                            .bind(this, app.app_id)
                            .call()}
                    />
                ))}
            </ul>
        );
    }
}
export default AsideApps;
