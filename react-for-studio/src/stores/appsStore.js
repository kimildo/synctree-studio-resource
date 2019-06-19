import { observable, computed, action } from 'mobx';
import _ from 'lodash';
import Request from '../library/utils/Request';

export default class appsStore {
    @observable apps = null;

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;

        if (
            this.rootStore.userInfoStore.isLoggedIn &&
            !!document.querySelector('#app')
        ) {
            this.loadApps();
        }
    }

    @computed get getApps() {
        return this.apps;
    }

    @action
    setApps(apps) {
        this.apps = apps;
        this.rootStore.userInfoStore.setSelectedApp(apps[0]);
    }

    @action
    loadApps() {
        this.request.get('/console/apps/list').then(res => {
            let { data } = res.data;
            this.setApps(data.apps);
        });
    }

    @action
    addApp(params) {
        this.request.post('/console/apps/add', params).then(res => {
            this.rootStore.modalStore.hideModal();
            this.loadApps();
        });
    }

    // 현재 Backend 구현 안됨
    @action
    deleteApp(appId) {
        this.request
            .post(`/console/apps/modifyCallback`, {
                app_id: appId,
                archive_flag: 1,
            })
            .then(res => {
                this.loadApps();
            });
    }

    @action
    resetApps() {
        this.apps = null;
    }
}
