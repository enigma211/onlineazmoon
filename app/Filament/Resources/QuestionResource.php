<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\QuestionBank;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $modelLabel = 'سوال';
    protected static ?string $pluralModelLabel = 'سوالات';
    protected static ?string $navigationLabel = 'ایجاد سوال';
    protected static ?string $navigationGroup = 'بانک سوالات';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('title')
                    ->required()
                    ->columnSpanFull()
                    ->label('متن سوال')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bullet-list',
                        'code',
                        'heading',
                        'italic',
                        'link',
                        'number-list',
                        'redo',
                        'strike',
                        'subscript',
                        'superscript',
                        'undo',
                        'formula',
                    ])
                    ->extraAttributes([
                        'x-data' => '',
                        'x-init' => '
                            // Add KaTeX support to the rich editor
                            const editor = $el.querySelector(".ProseMirror");
                            if (editor) {
                                // Add formula button functionality
                                const observer = new MutationObserver(() => {
                                    renderMathInElement(editor, {
                                        delimiters: [
                                            {left: "$$", right: "$$", display: true},
                                            {left: "$", right: "$", display: false}
                                        ]
                                    });
                                });
                                observer.observe(editor, { childList: true, subtree: true });
                            }
                        '
                    ])
                    ->helperText('برای نوشتن فرمول می‌توانید از قالب $$...$$ استفاده کنید. مثال: $$x^2 + y^2 = z^2$$'),
                Forms\Components\FileUpload::make('image')
                    ->label('تصویر سوال')
                    ->image()
                    ->directory('questions')
                    ->columnSpanFull(),
                Forms\Components\Select::make('question_bank_id')
                    ->label('بانک سوالات')
                    ->relationship('questionBank', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('category', null)),
                Forms\Components\Select::make('category')
                    ->label('دسته‌بندی')
                    ->options(function (Get $get): array {
                        $questionBankId = $get('question_bank_id');

                        if (!$questionBankId) {
                            return [];
                        }

                        $questionBank = QuestionBank::query()->find($questionBankId);

                        if (!$questionBank) {
                            return [];
                        }

                        $categories = Question::query()
                            ->where('question_bank_id', $questionBankId)
                            ->whereNotNull('category')
                            ->where('category', '!=', '')
                            ->distinct()
                            ->pluck('category')
                            ->values()
                            ->toArray();

                        if (filled($questionBank->category) && !in_array($questionBank->category, $categories, true)) {
                            $categories[] = $questionBank->category;
                        }

                        return collect($categories)
                            ->mapWithKeys(fn (string $category): array => [$category => $category])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('question_bank_id')))
                    ->helperText('ابتدا بانک سوالات را انتخاب کنید تا دسته‌بندی‌های مرتبط نمایش داده شوند.'),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\RichEditor::make('option_1')
                            ->label('گزینه ۱')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'subscript',
                                'superscript',
                                'formula',
                            ])
                            ->extraAttributes([
                                'x-data' => '',
                                'x-init' => '
                                    const editor = $el.querySelector(".ProseMirror");
                                    if (editor) {
                                        const observer = new MutationObserver(() => {
                                            renderMathInElement(editor, {
                                                delimiters: [
                                                    {left: "$$", right: "$$", display: true},
                                                    {left: "$", right: "$", display: false}
                                                ]
                                            });
                                        });
                                        observer.observe(editor, { childList: true, subtree: true });
                                    }
                                '
                            ])
                            ->helperText('می‌توانید از فرمول استفاده کنید'),
                        Forms\Components\RichEditor::make('option_2')
                            ->label('گزینه ۲')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'subscript',
                                'superscript',
                                'formula',
                            ])
                            ->extraAttributes([
                                'x-data' => '',
                                'x-init' => '
                                    const editor = $el.querySelector(".ProseMirror");
                                    if (editor) {
                                        const observer = new MutationObserver(() => {
                                            renderMathInElement(editor, {
                                                delimiters: [
                                                    {left: "$$", right: "$$", display: true},
                                                    {left: "$", right: "$", display: false}
                                                ]
                                            });
                                        });
                                        observer.observe(editor, { childList: true, subtree: true });
                                    }
                                '
                            ])
                            ->helperText('می‌توانید از فرمول استفاده کنید'),
                        Forms\Components\RichEditor::make('option_3')
                            ->label('گزینه ۳')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'subscript',
                                'superscript',
                                'formula',
                            ])
                            ->extraAttributes([
                                'x-data' => '',
                                'x-init' => '
                                    const editor = $el.querySelector(".ProseMirror");
                                    if (editor) {
                                        const observer = new MutationObserver(() => {
                                            renderMathInElement(editor, {
                                                delimiters: [
                                                    {left: "$$", right: "$$", display: true},
                                                    {left: "$", right: "$", display: false}
                                                ]
                                            });
                                        });
                                        observer.observe(editor, { childList: true, subtree: true });
                                    }
                                '
                            ])
                            ->helperText('می‌توانید از فرمول استفاده کنید'),
                        Forms\Components\RichEditor::make('option_4')
                            ->label('گزینه ۴')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'subscript',
                                'superscript',
                                'formula',
                            ])
                            ->extraAttributes([
                                'x-data' => '',
                                'x-init' => '
                                    const editor = $el.querySelector(".ProseMirror");
                                    if (editor) {
                                        const observer = new MutationObserver(() => {
                                            renderMathInElement(editor, {
                                                delimiters: [
                                                    {left: "$$", right: "$$", display: true},
                                                    {left: "$", right: "$", display: false}
                                                ]
                                            });
                                        });
                                        observer.observe(editor, { childList: true, subtree: true });
                                    }
                                '
                            ])
                            ->helperText('می‌توانید از فرمول استفاده کنید'),
                    ]),
                Forms\Components\Select::make('correct_option')
                    ->label('گزینه صحیح')
                    ->options([
                        1 => 'گزینه ۱',
                        2 => 'گزینه ۲',
                        3 => 'گزینه ۳',
                        4 => 'گزینه ۴',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Section::make('راهنمای فرمول‌نویسی')
                    ->description('نمونه‌های آماده برای فرمول‌نویسی')
                    ->schema([
                        Forms\Components\Placeholder::make('formula_guide')
                            ->content(function () {
                                return new HtmlString(<<<'HTML'
                                    <div class="space-y-4" dir="ltr">
                                        <div class="bg-blue-50 p-4 rounded-lg">
                                            <h4 class="font-bold text-blue-900 mb-2">نمونه‌های فرمول:</h4>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$x^2 + y^2 = z^2$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-1"></div>
                                                </div>
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$\\frac{a}{b} = \\frac{c}{d}$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-2"></div>
                                                </div>
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$\\sqrt{x^2 + 1}$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-3"></div>
                                                </div>
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$\\sum_{i=1}^{n} x_i$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-4"></div>
                                                </div>
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$\\int_0^\\infty e^{-x} dx$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-5"></div>
                                                </div>
                                                <div>
                                                    <code class="bg-gray-100 p-1 rounded">$$\\alpha + \\beta = \\gamma$$</code>
                                                    <div class="mt-1 p-2 bg-white border rounded" id="preview-6"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
                                    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const examples = [
                                                ["preview-1", "x^2 + y^2 = z^2"],
                                                ["preview-2", "\\frac{a}{b} = \\frac{c}{d}"],
                                                ["preview-3", "\\sqrt{x^2 + 1}"],
                                                ["preview-4", "\\sum_{i=1}^{n} x_i"],
                                                ["preview-5", "\\int_0^\\infty e^{-x} dx"],
                                                ["preview-6", "\\alpha + \\beta = \\gamma"]
                                            ];

                                            examples.forEach(([id, formula]) => {
                                                const element = document.getElementById(id);
                                                if (element && katex) {
                                                    katex.render(formula, element, {
                                                        throwOnError: false
                                                    });
                                                }
                                            });
                                        });
                                    </script>
                                HTML);
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('تصویر'),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->formatStateUsing(function ($state): string {
                        $title = trim(preg_replace('/\s+/', ' ', strip_tags((string) $state)) ?? '');

                        return $title !== '' ? $title : '-';
                    })
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correct_option')
                    ->label('گزینه صحیح')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->formatStateUsing(fn ($state) => $state ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($state))->format('Y/m/d H:i') : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->options(function () {
                        return \App\Models\Question::distinct()->pluck('category', 'category');
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
