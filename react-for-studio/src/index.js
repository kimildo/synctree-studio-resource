import React from 'react';
import ReactDOM from 'react-dom';
import { registerObserver } from 'react-perf-devtool';
import { HashRouter as Router } from 'react-router-dom';
import { configure } from 'mobx';
import { Provider } from 'mobx-react';
import ClipboardJS from 'clipboard';
import App from './App';
import Document from './Document';
import stores from './stores';
import docsStores from './Document/stores';

import Util from './library/utils/Util';

// pref chrome extension use : NODE_ENV development 환경에서만 작동함
registerObserver();

// state의 상태는 action을 통해서만 가능하게끔 셋팅
configure({
    enforceActions: true,
});

const clipboard = new ClipboardJS('.copy-btn');

clipboard.on('success', e => {
    Util.showSmallBox('success_message', 3000, 'Copied!! - ' + e.text);
    e.clearSelection();
});
clipboard.on('error', e => {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});

// const root = document.querySelector('#bunit-modify-wrap');
const root = document.querySelector('#app');

const doc = document.querySelector('#document');

switch (true) {
    case !!root:
        ReactDOM.render(
            <Provider {...stores}>
                <Router>
                    <App />
                </Router>
            </Provider>,
            root
        );
        break;
    case !!doc:
        ReactDOM.render(
            <Provider {...docsStores}>
                <Document />
            </Provider>,
            doc
        );
        break;
    default:
        console.log('react dom render failed');
        break;
}
