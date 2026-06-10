import type { ReactNode } from 'react';

const comics = [
  {
    title: 'Solo Farming In The Tower',
    chapter: '128',
    status: 'Hot update',
    reads: '31.4K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2023/04/solo-farming-in-the-tower-657379-fb8qKjvk.jpg',
  },
  {
    title: 'Chronicles Of The Martial God',
    chapter: '161',
    status: 'Top rank',
    reads: '52.8K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2022/06/a421797d-f755-48b4-a357-b5d0b1c7a94e-991136-Y09ccVdJ.jpg',
  },
  {
    title: 'Tabib Bocil Yang Tidak Menyembunyikan Kejeniusannya',
    chapter: '41',
    status: 'New arc',
    reads: '18.2K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2025/12/Cover_Tabib_Bocil.jpg',
  },
  {
    title: 'Entomologist in Sichuan Tang Clan',
    chapter: '89',
    status: 'Trending',
    reads: '24.7K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2024/11/entopologist-tang-clan-e1730653284236-150164-1Fiug4dV.png',
  },
  {
    title: 'Bocil Dari Keluarga Villain',
    chapter: '47',
    status: 'Ongoing',
    reads: '16.9K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2026/01/Cover_Bocil_Villain-scaled.png',
  },
  {
    title: 'Jadi Kasir di Dunia Lain',
    chapter: '101',
    status: 'Ongoing',
    reads: '20.1K',
    cover:
      'https://05.ikiru.wtf/wp-content/uploads/2024/05/cvr-211724-OszH0QCD.jpg',
  },
];

const ranking = comics.slice(0, 4);

const Icon = ({ children }: { children: ReactNode }) => (
  <span className="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-[#f1f4ef] text-[#151515]">
    {children}
  </span>
);

export const KomikHubModernCatalog = () => {
  return (
    <div className="min-h-screen w-full bg-[#f8f7f1] text-[#151515] antialiased">
      <div className="mx-auto grid min-h-screen w-full max-w-[1440px] grid-cols-[248px_minmax(0,1fr)]">
        <aside className="flex min-h-screen flex-col border-r border-[#dedbd0] bg-[#fffdf7] px-6 py-7">
          <div className="flex items-center gap-3">
            <div className="grid h-11 w-11 place-items-center rounded-lg bg-[#151515] font-black text-white">
              KH
            </div>
            <div>
              <p className="text-lg font-black leading-none">KomikHub</p>
              <p className="mt-1 text-xs font-semibold uppercase text-[#74716a]">Reader library</p>
            </div>
          </div>

          <nav className="mt-10 grid gap-2 text-sm font-bold">
            {['Beranda', 'Update', 'Bookmark', 'Ranking', 'Admin'].map((item, index) => (
              <button
                className={`flex h-11 items-center justify-between rounded-lg px-4 text-left transition-[background-color,transform] duration-200 active:scale-[0.96] ${
                  index === 0 ? 'bg-[#151515] text-white' : 'text-[#494640] hover:bg-[#f1f4ef]'
                }`}
                key={item}
              >
                <span>{item}</span>
                {index === 0 ? <span className="h-2 w-2 rounded-full bg-[#f4c95d]" /> : null}
              </button>
            ))}
          </nav>

          <div className="mt-auto rounded-lg border border-[#dedbd0] bg-[#f1f4ef] p-4">
            <p className="text-xs font-black uppercase text-[#74716a]">Reading stats</p>
            <div className="mt-4 grid grid-cols-2 gap-3">
              <div>
                <p className="font-black tabular-nums">761</p>
                <p className="text-xs text-[#74716a]">Chapter</p>
              </div>
              <div>
                <p className="font-black tabular-nums">24</p>
                <p className="text-xs text-[#74716a]">Series</p>
              </div>
            </div>
          </div>
        </aside>

        <main className="min-w-0">
          <header className="flex h-20 items-center gap-4 border-b border-[#dedbd0] bg-[#f8f7f1]/90 px-8">
            <div className="flex h-11 flex-1 items-center gap-3 rounded-lg border border-[#d8d4c8] bg-white px-4 shadow-[0_1px_0_rgba(0,0,0,0.04)]">
              <span className="text-[#74716a]">Search</span>
              <input
                className="h-full flex-1 bg-transparent text-sm font-semibold outline-none placeholder:text-[#9b978e]"
                placeholder="Cari judul, chapter, atau author"
              />
            </div>
            <button className="h-11 rounded-lg bg-[#e83f3a] px-5 text-sm font-black text-white shadow-[0_10px_20px_rgba(232,63,58,0.22)] transition-[transform,filter] duration-200 hover:filter active:scale-[0.96]">
              Cari
            </button>
            <button className="h-11 rounded-lg border border-[#d8d4c8] bg-white px-4 text-sm font-black transition-[background-color,transform] duration-200 hover:bg-[#f1f4ef] active:scale-[0.96]">
              Login
            </button>
          </header>

          <div className="px-8 py-7">
            <section className="grid grid-cols-[minmax(0,1fr)_348px] gap-6">
              <div className="relative min-h-[342px] overflow-hidden rounded-lg bg-[#151515] text-white">
                <img
                  alt="Featured comic cover"
                  className="absolute inset-0 h-full w-full object-cover opacity-54"
                  src={comics[0].cover}
                />
                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(21,21,21,0.92)_0%,rgba(21,21,21,0.74)_42%,rgba(21,21,21,0.08)_100%)]" />
                <div className="relative flex h-full max-w-[650px] flex-col justify-end p-8">
                  <div className="mb-5 flex gap-2">
                    <span className="rounded-md bg-[#f4c95d] px-3 py-1 text-xs font-black uppercase text-[#151515]">
                      Hot update
                    </span>
                    <span className="rounded-md bg-white/12 px-3 py-1 text-xs font-black uppercase text-white">
                      Chapter 128
                    </span>
                  </div>
                  <h1 className="max-w-[560px] text-[56px] font-black leading-[1.02]">
                    Solo Farming In The Tower
                  </h1>
                  <p className="mt-4 max-w-[520px] text-base leading-7 text-white/78">
                    Layout utama menaruh cover dan judul sebagai fokus, lalu aksi baca dan metadata tetap mudah
                    dipindai tanpa terasa penuh.
                  </p>
                  <div className="mt-7 flex flex-wrap gap-3">
                    <button className="h-11 rounded-lg bg-[#e83f3a] px-5 text-sm font-black text-white transition-[transform,filter] duration-200 hover:filter active:scale-[0.96]">
                      Baca Sekarang
                    </button>
                    <button className="h-11 rounded-lg bg-white px-5 text-sm font-black text-[#151515] transition-[transform,background-color] duration-200 hover:bg-[#f8f7f1] active:scale-[0.96]">
                      Simpan
                    </button>
                  </div>
                </div>
              </div>

              <aside className="rounded-lg border border-[#dedbd0] bg-white p-5">
                <div className="flex items-center justify-between">
                  <h2 className="text-xl font-black">Ranking</h2>
                  <button className="rounded-lg bg-[#f1f4ef] px-3 py-2 text-xs font-black transition-[transform] duration-200 active:scale-[0.96]">
                    Minggu ini
                  </button>
                </div>
                <div className="mt-5 grid gap-4">
                  {ranking.map((comic, index) => (
                    <div className="grid grid-cols-[34px_54px_minmax(0,1fr)] items-center gap-3" key={comic.title}>
                      <div className="font-black tabular-nums text-[#e83f3a]">0{index + 1}</div>
                      <img
                        alt={comic.title}
                        className="h-16 w-[54px] rounded-md object-cover"
                        src={comic.cover}
                      />
                      <div className="min-w-0">
                        <p className="truncate text-sm font-black">{comic.title}</p>
                        <p className="mt-1 text-xs font-semibold text-[#74716a]">
                          {comic.reads} views / Ch. {comic.chapter}
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </aside>
            </section>

            <section className="mt-7 grid grid-cols-[minmax(0,1fr)_276px] gap-6">
              <div>
                <div className="mb-4 flex items-center justify-between gap-4">
                  <div>
                    <h2 className="text-2xl font-black">Update terbaru</h2>
                    <p className="mt-1 text-sm font-semibold text-[#74716a]">
                      Grid cover padat, jelas, dan siap dipakai untuk katalog komik.
                    </p>
                  </div>
                  <div className="flex rounded-lg border border-[#dedbd0] bg-white p-1">
                    {['Terbaru', 'Populer', 'A-Z'].map((label, index) => (
                      <button
                        className={`h-9 rounded-md px-4 text-sm font-black transition-[background-color,transform] duration-200 active:scale-[0.96] ${
                          index === 0 ? 'bg-[#151515] text-white' : 'text-[#74716a]'
                        }`}
                        key={label}
                      >
                        {label}
                      </button>
                    ))}
                  </div>
                </div>

                <div className="grid grid-cols-3 gap-5">
                  {comics.map((comic) => (
                    <article className="overflow-hidden rounded-lg border border-[#dedbd0] bg-white" key={comic.title}>
                      <div className="relative aspect-[3/4] overflow-hidden bg-[#151515]">
                        <img alt={comic.title} className="h-full w-full object-cover" src={comic.cover} />
                        <div className="absolute left-3 top-3 rounded-md bg-white px-2.5 py-1 text-xs font-black text-[#151515]">
                          {comic.status}
                        </div>
                      </div>
                      <div className="p-4">
                        <h3 className="line-clamp-2 min-h-12 text-base font-black leading-6">{comic.title}</h3>
                        <div className="mt-4 flex items-center justify-between border-t border-[#ece8dc] pt-3 text-xs font-bold text-[#74716a]">
                          <span>Ch. {comic.chapter}</span>
                          <span>{comic.reads} views</span>
                        </div>
                      </div>
                    </article>
                  ))}
                </div>
              </div>

              <aside className="grid content-start gap-4">
                <div className="rounded-lg bg-[#2fb6a3] p-5 text-white">
                  <Icon>V</Icon>
                  <h3 className="mt-5 text-xl font-black">Voting lanjut</h3>
                  <p className="mt-2 text-sm font-semibold leading-6 text-white/82">
                    Area voting dibuat sebagai blok fungsi, bukan dekorasi, supaya user tahu komik mana yang layak
                    diprioritaskan.
                  </p>
                </div>
                <div className="rounded-lg bg-[#f4c95d] p-5 text-[#151515]">
                  <Icon>B</Icon>
                  <h3 className="mt-5 text-xl font-black">Bookmark cepat</h3>
                  <p className="mt-2 text-sm font-semibold leading-6 text-[#454039]">
                    Aksi simpan tetap dekat dengan cover dan detail tanpa mengganggu ritme scan katalog.
                  </p>
                </div>
              </aside>
            </section>
          </div>
        </main>
      </div>

      <div className="fixed bottom-0 left-0 right-0 hidden border-t border-[#dedbd0] bg-white px-4 py-3 max-[820px]:block">
        <div className="mx-auto grid max-w-[420px] grid-cols-4 gap-2 text-center text-xs font-black text-[#74716a]">
          {['Home', 'Cari', 'Rank', 'User'].map((item, index) => (
            <button
              className={`h-11 rounded-lg transition-[background-color,transform] duration-200 active:scale-[0.96] ${
                index === 0 ? 'bg-[#151515] text-white' : 'bg-[#f1f4ef]'
              }`}
              key={item}
            >
              {item}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
};
