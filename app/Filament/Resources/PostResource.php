<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->native()
                    ->reactive()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('Generate')
                            ->disabled(fn(callable $get) => blank($get('category_id')))
                            ->label('Generate Content with AI')
                            ->icon('heroicon-o-light-bulb')
                            ->tooltip('Generate post content using AI based on the selected category.')
                            ->requiresConfirmation()
                            ->action(function (callable $set, callable $get) {
                                $id = $get('category_id');

                                if (empty($id)) {
                                    return;
                                }

                                $category = \App\Models\PostCategory::find($id)?->name ?? 'General';

                                $response = app('llm')->generateBlogContent($category);

                                $set('title', $response['title'] ?? '');
                                $set('content', $response['content'] ?? '');
                            }),
                    ),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('post_images')
                    ->disk('public')
                    ->label('Post Image')
                    ->multiple()
                    ->image()
                    ->previewable(false)
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/*'])
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_published')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->title)
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image')
                    ->label('Category Image')
                    ->extraImgAttributes(['class' => 'rounded-md'])
                    ->height(70)
                    ->getStateUsing(function ($record) {
                        return $record->getFirstMediaUrl('post_images', 'preview')
                            ?: asset('images/default-category.png');
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                Filter::make('is_published')
                    ->label('Published Status')
                    ->query(fn(Builder $query): Builder => $query->where('is_published', true))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->latest(); // same as orderBy('created_at', 'desc')
    }
}
