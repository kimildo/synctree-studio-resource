import MyLoadable from '../MyLoadable';

export const Intro = MyLoadable({
    loader: () => import(/* webpackChunkName: "i-p" */ './Intro'),
});

export const Auth = MyLoadable({
    loader: () => import(/* webpackChunkName: "a-p" */ './Auth'),
});

export const Home = MyLoadable({
    loader: () => import(/* webpackChunkName: "h-p" */ './Home'),
});

export const B2b = MyLoadable({
    loader: () => import(/* webpackChunkName: "b2-p" */ './B2b'),
});

export const Dashboard = MyLoadable({
    loader: () => import(/* webpackChunkName: "d-p" */ './Dashboard'),
});

export const Management = MyLoadable({
    loader: () => import(/* webpackChunkName: "mg-p" */ './Management'),
});
export const Marketing = MyLoadable({
    loader: () => import(/* webpackChunkName: "mt-p" */ './Marketing'),
});
export const Apps = MyLoadable({
    loader: () => import(/* webpackChunkName: "a-p" */ './Apps'),
});
export const BizUnit = MyLoadable({
    loader: () => import(/* webpackChunkName: "bu-p" */ './BizUnit'),
});
export const Console = MyLoadable({
    loader: () => import(/* webpackChunkName: "c-p" */ './Console'),
});
export const BunitModify = MyLoadable({
    loader: () => import(/* webpackChunkName: "bm-p" */ './BunitModify'),
});
export const Partner = MyLoadable({
    loader: () => import(/* webpackChunkName: "p-p" */ './Partner'),
});
export const Operator = MyLoadable({
    loader: () => import(/* webpackChunkName: "op-p" */ './Operator'),
});

export { default as Error404 } from './Error404';
