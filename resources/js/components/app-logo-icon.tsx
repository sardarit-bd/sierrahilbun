import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(
    props: ImgHTMLAttributes<HTMLImageElement>
) {
    return (
        <img
            src="/images/turftec-logo.png"
            alt="App Logo"
            {...props}
        />
    );
}
