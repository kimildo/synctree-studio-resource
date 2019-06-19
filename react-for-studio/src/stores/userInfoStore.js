import { observable, computed, action } from 'mobx';
import _ from 'lodash';
import Request from '../library/utils/Request';

export default class userInfoStore {
    @observable userInfo = null;
    @observable selectedApp = null;
    userInfoKey = 'StudioUserInfo';

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
        this.userInfo =
            JSON.parse(sessionStorage.getItem(this.userInfoKey)) || null;
    }

    @computed get isLoggedIn() {
        return !!this.userInfo;
    }

    @computed get getUserInfo() {
        return this.userInfo;
    }
    @computed get getSelectedApp() {
        return this.selectedApp;
    }

    @action
    setUserInfo(userInfo) {
        sessionStorage.setItem(this.userInfoKey, JSON.stringify(userInfo));
        this.userInfo = userInfo;
    }
    @action
    setSelectedApp(app) {
        this.selectedApp = app;
    }
    @action
    setSelectedAppByAppId(appId) {
        if (this.selectedApp !== null) {
            if (this.selectedApp.app_id !== appId) {
                this.setSelectedApp(
                    _.find(this.rootStore.appsStore.getApps, {
                        app_id: appId,
                    })
                );
                this.rootStore.bizOpsStore.loadBizOps(appId);
            }
        }
    }
    @action
    signOut() {
        this.request.get('/auth/signout').then(res => {
            this.rootStore.navStore.resetNav();
            this.rootStore.appsStore.resetApps();
            this.rootStore.bizOpsStore.resetOps();
            this.rootStore.bizStore.unsetBizUnit();
            this.rootStore.opStore.unsetOperator();
            this.rootStore.opsStore.unsetOperators();
            this.rootStore.mappingStore.unsetMapping();
            this.rootStore.operatorStore.unsetOperators();
            this.unsetUserInfo();
        });
    }

    @action
    unsetUserInfo() {
        sessionStorage.removeItem(this.userInfoKey);
        this.userInfo = null;
    }
}
