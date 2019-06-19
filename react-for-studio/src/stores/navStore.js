import { observable, computed, action } from 'mobx';
/* global global_data $, showSmallBox */

export default class navStore {
    @observable nav = [];

    constructor(rootStore) {
        this.rootStore = rootStore;
    }

    @computed get getNav() {
        return this.nav;
    }

    @action
    setNav(navArr) {
        this.nav = navArr;
    }

    @action
    resetNav() {
        this.nav = [];
    }
}
