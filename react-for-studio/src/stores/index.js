import RootStore from './RootStore';
const rootStore = new RootStore();

const stores = {
    rootStroe: rootStore,
    userInfoStore: rootStore.userInfoStore,
    navStore: rootStore.navStore,
    appsStore: rootStore.appsStore,
    modalStore: rootStore.modalStore,
    bizOpsStore: rootStore.bizOpsStore,
    bizStore: rootStore.bizStore,
    opStore: rootStore.opStore,
    opsStore: rootStore.opsStore,
    mappingStore: rootStore.mappingStore,
    shareStore: rootStore.shareStore,
    partnerStore: rootStore.partnerStore,
    operatorStore: rootStore.operatorStore,
    alterStore: rootStore.alterStore,
};

export default stores;
