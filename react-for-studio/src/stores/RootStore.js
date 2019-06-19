import { BizStore, OpStore, OpsStore, MappingStore } from './bizUnit';

import userInfoStore from './userInfoStore';
import navStore from './navStore';
import appsStore from './appsStore';
import ModalStore from './ModalStore';
import bizOpsStore from './bizOpsStore';
import shareStore from './shareStore';
import partnerStore from './partnerStore';
import operatorStore from './operatorStore';
import alterStore from './AlterStore';

export default class RootStore {
    constructor() {
        this.userInfoStore = new userInfoStore(this);
        this.navStore = new navStore(this);
        this.appsStore = new appsStore(this);
        this.modalStore = new ModalStore(this);
        this.bizOpsStore = new bizOpsStore(this);
        this.bizStore = new BizStore(this);
        this.opStore = new OpStore(this);
        this.opsStore = new OpsStore(this);
        this.mappingStore = new MappingStore(this);
        this.shareStore = new shareStore(this);
        this.partnerStore = new partnerStore(this);
        this.operatorStore = new operatorStore(this);
        this.alterStore = new alterStore(this);
    }
}
