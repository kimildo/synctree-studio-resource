import { observable, computed, action } from 'mobx';
import _ from 'lodash';
import Request from '../library/utils/Request';

export default class partnerStore {
    @observable baseInfo = null;
    @observable userInfo = null;
    @observable code = null;
    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
        this.userInfoKey = 'StudioPartnerInfo';
        this.userInfo =
            JSON.parse(sessionStorage.getItem(this.userInfoKey)) || null;
    }
    @computed get isLoggedIn() {
        return !!this.userInfo;
    }
    @computed get getUserInfo() {
        return this.userInfo;
    }
    @computed get getBaseInfo() {
        return this.baseInfo;
    }
    @computed get getcode() {
        return this.code;
    }
    @action
    setUserInfo(userInfo) {
        sessionStorage.setItem(this.userInfoKey, JSON.stringify(userInfo));
        this.userInfo = userInfo;
    }
    @action
    setBaseInfo(baseInfo) {
        this.baseInfo = baseInfo;
    }

    @action
    setCode(code) {
        this.code = code;
    }
}
